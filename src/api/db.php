<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname($GLOBALS['DB_PATH']);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . $GLOBALS['DB_PATH']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA journal_mode=WAL;');
        init_schema($pdo);
    }
    return $pdo;
}

function init_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            status TEXT NOT NULL DEFAULT 'aguardando',
            word TEXT,
            max_attempts INTEGER,
            guessed_letters TEXT NOT NULL DEFAULT '',
            wrong_guesses INTEGER NOT NULL DEFAULT 0,
            current_player_idx INTEGER NOT NULL DEFAULT 0,
            turn_started_at TEXT,
            started_at TEXT,
            ended_at TEXT,
            winner_nickname TEXT
        );

        CREATE TABLE IF NOT EXISTS players (
            token TEXT PRIMARY KEY,
            nickname TEXT NOT NULL,
            room_id INTEGER,
            joined_at TEXT NOT NULL DEFAULT (datetime('now')),
            position INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (room_id) REFERENCES rooms(id)
        );

        CREATE TABLE IF NOT EXISTS bans (
            token TEXT PRIMARY KEY,
            nickname TEXT NOT NULL,
            banned_at TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS ranking (
            nickname TEXT PRIMARY KEY,
            games_played INTEGER NOT NULL DEFAULT 0,
            games_won INTEGER NOT NULL DEFAULT 0,
            total_letters_guessed INTEGER NOT NULL DEFAULT 0
        );

        CREATE INDEX IF NOT EXISTS idx_players_room ON players(room_id);
    ");

    $count = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO rooms (id) VALUES (1)");
    }
}

function generate_token(): string {
    return bin2hex(random_bytes(16));
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_method(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_response(['error' => 'method_not_allowed'], 405);
    }
}

function get_json_body(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function get_masked_word(string $word, string $guessed): string {
    $guessed_arr = array_filter(array_map('trim', explode(',', $guessed)));
    $guessed_lower = array_map('mb_strtolower', $guessed_arr);
    $result = '';
    $len = mb_strlen($word);
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($word, $i, 1);
        if ($ch === ' ' || $ch === '-') {
            $result .= $ch;
        } elseif (in_array(mb_strtolower($ch), $guessed_lower, true)) {
            $result .= $ch;
        } else {
            $result .= '_';
        }
        if ($i < $len - 1) $result .= ' ';
    }
    return $result;
}

function is_word_complete(string $word, string $guessed): bool {
    $guessed_arr = array_map('mb_strtolower', array_filter(array_map('trim', explode(',', $guessed))));
    $len = mb_strlen($word);
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($word, $i, 1);
        if ($ch === ' ' || $ch === '-') continue;
        if (!in_array(mb_strtolower($ch), $guessed_arr, true)) return false;
    }
    return true;
}

function get_room_players(PDO $pdo, int $room_id): array {
    $stmt = $pdo->prepare("SELECT token, nickname, position, joined_at FROM players WHERE room_id = ? ORDER BY position ASC");
    $stmt->execute([$room_id]);
    $players = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $players[] = [
            'token' => $row['token'],
            'nickname' => $row['nickname'],
            'position' => (int)$row['position'],
            'joined_at' => $row['joined_at'],
        ];
    }
    return $players;
}

function build_client_state(PDO $pdo, int $room_id, string $token): ?array {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$room) return null;

    $stmt = $pdo->prepare("SELECT 1 FROM players WHERE token = ? AND room_id = ?");
    $stmt->execute([$token, $room_id]);
    if (!$stmt->fetchColumn()) return null;

    $players = get_room_players($pdo, $room_id);
    $current_idx = (int)$room['current_player_idx'];
    $turn_remaining = null;
    if ($room['turn_started_at'] && $room['status'] === 'em_jogo') {
        $turn_remaining = max(0, $GLOBALS['TURN_SECONDS'] - (time() - strtotime($room['turn_started_at'])));
    }
    $your_turn = false;
    if ($room['status'] === 'em_jogo' && isset($players[$current_idx])) {
        $your_turn = $players[$current_idx]['token'] === $token;
    }

    return [
        'room_id' => $room_id,
        'status' => $room['status'],
        'word_masked' => $room['word'] ? get_masked_word($room['word'], $room['guessed_letters']) : null,
        'guessed_letters' => array_values(array_filter(array_map('trim', explode(',', $room['guessed_letters'])))),
        'wrong_guesses' => (int)$room['wrong_guesses'],
        'max_attempts' => $room['max_attempts'] ? (int)$room['max_attempts'] : null,
        'players' => array_map(function ($p) use ($current_idx, $token) {
            return [
                'nickname' => $p['nickname'],
                'position' => $p['position'],
                'is_self' => $p['token'] === $token,
                'is_current' => $p['position'] === $current_idx,
            ];
        }, $players),
        'current_player_idx' => $current_idx,
        'turn_remaining_seconds' => $turn_remaining,
        'your_turn' => $your_turn,
        'winner_nickname' => $room['winner_nickname'],
    ];
}

function update_ranking(PDO $pdo, int $room_id, ?string $winner_nickname): void {
    $stmt = $pdo->prepare("SELECT DISTINCT nickname FROM players WHERE room_id = ?");
    $stmt->execute([$room_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nick) {
        $pdo->prepare("INSERT INTO ranking (nickname, games_played, games_won, total_letters_guessed) VALUES (?, 1, 0, 0) ON CONFLICT(nickname) DO UPDATE SET games_played = games_played + 1")
            ->execute([$nick]);
    }
    if ($winner_nickname) {
        $pdo->prepare("INSERT INTO ranking (nickname, games_played, games_won, total_letters_guessed) VALUES (?, 0, 1, 0) ON CONFLICT(nickname) DO UPDATE SET games_won = games_won + 1")
            ->execute([$winner_nickname]);
    }
}
