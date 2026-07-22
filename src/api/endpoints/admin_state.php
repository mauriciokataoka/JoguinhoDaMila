<?php
require_method('GET');
$token = trim($_GET['token'] ?? '');
if (!hash_equals($GLOBALS['ADMIN_TOKEN'], $token)) {
    json_response(['error' => 'unauthorized'], 401);
}

$pdo = get_db();

$rooms = [];
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $players = get_room_players($pdo, (int)$row['id']);
    $turn_remaining = null;
    if ($row['turn_started_at'] && $row['status'] === 'em_jogo') {
        $turn_remaining = max(0, $GLOBALS['TURN_SECONDS'] - (time() - strtotime($row['turn_started_at'])));
    }
    $rooms[] = [
        'id' => (int)$row['id'],
        'status' => $row['status'],
        'word' => $row['word'],
        'max_attempts' => $row['max_attempts'] ? (int)$row['max_attempts'] : null,
        'guessed_letters' => $row['guessed_letters'],
        'wrong_guesses' => (int)$row['wrong_guesses'],
        'word_masked' => $row['word'] ? get_masked_word($row['word'], $row['guessed_letters']) : null,
        'current_player_idx' => (int)$row['current_player_idx'],
        'turn_started_at' => $row['turn_started_at'],
        'turn_remaining_seconds' => $turn_remaining,
        'players' => $players,
        'winner_nickname' => $row['winner_nickname'],
        'started_at' => $row['started_at'],
        'ended_at' => $row['ended_at'],
    ];
}

$last_room = end($rooms);
$banned = [];
$stmt = $pdo->query("SELECT token, nickname, banned_at FROM bans ORDER BY banned_at DESC LIMIT 50");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $banned[] = ['token' => $row['token'], 'nickname' => $row['nickname'], 'banned_at' => $row['banned_at']];
}
json_response([
    'admin_name' => $GLOBALS['ADMIN_NAME'],
    'rooms' => $rooms,
    'last_room_id' => $last_room ? (int)$last_room['id'] : null,
    'banned' => $banned,
]);
