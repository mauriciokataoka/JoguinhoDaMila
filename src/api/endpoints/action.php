<?php
require_method('POST');
$body = get_json_body();
$room_id = (int)($body['room'] ?? 0);
$token = trim($body['token'] ?? '');
$action_type = trim($body['type'] ?? '');
$payload = $body['payload'] ?? [];

if ($room_id <= 0 || $token === '' || $action_type === '') {
    json_response(['error' => 'invalid_params'], 400);
}

$pdo = get_db();

$stmt = $pdo->prepare("SELECT 1 FROM bans WHERE token = ?");
$stmt->execute([$token]);
if ($stmt->fetchColumn()) {
    json_response(['error' => 'banned'], 403);
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$room) {
    json_response(['error' => 'room_not_found'], 404);
}

$stmt = $pdo->prepare("SELECT * FROM players WHERE token = ? AND room_id = ?");
$stmt->execute([$token, $room_id]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$player) {
    json_response(['error' => 'not_in_room'], 403);
}

if ($action_type === 'guess_letter') {
    if ($room['status'] !== 'em_jogo') {
        json_response(['error' => 'game_not_active'], 400);
    }
    $letter = mb_strtolower(trim($payload['letter'] ?? ''));
    if (mb_strlen($letter) !== 1 || !ctype_alpha($letter)) {
        json_response(['error' => 'invalid_letter'], 400);
    }

    $players_in_room = get_room_players($pdo, $room_id);
    if (empty($players_in_room)) {
        json_response(['error' => 'no_players'], 400);
    }
    $current_idx = (int)$room['current_player_idx'] % count($players_in_room);
    if ($players_in_room[$current_idx]['token'] !== $token) {
        json_response(['error' => 'not_your_turn'], 400);
    }

    $guessed = array_values(array_filter(array_map('trim', explode(',', $room['guessed_letters']))));
    $guessed_lower = array_map('mb_strtolower', $guessed);
    if (in_array($letter, $guessed_lower, true)) {
        json_response(['error' => 'already_guessed'], 400);
    }

    $guessed[] = $letter;
    $guessed_csv = implode(',', $guessed);

    $word_lower = mb_strtolower($room['word']);
    $is_hit = mb_strpos($word_lower, $letter) !== false;

    $wrong = (int)$room['wrong_guesses'];
    if (!$is_hit) $wrong++;

    $ended = false;
    $winner_nickname = null;
    $new_status = $room['status'];
    $new_current_idx = ($current_idx + 1) % count($players_in_room);
    $now = date('Y-m-d H:i:s');

    if (is_word_complete($room['word'], $guessed_csv)) {
        $ended = true;
        $winner_nickname = $player['nickname'];
        $new_status = 'encerrada';
    } elseif ($wrong >= (int)$room['max_attempts']) {
        $ended = true;
        $new_status = 'encerrada';
    }

    if ($ended) {
        $stmt = $pdo->prepare("UPDATE rooms SET guessed_letters = ?, wrong_guesses = ?, current_player_idx = ?, turn_started_at = NULL, status = ?, ended_at = ?, winner_nickname = ? WHERE id = ?");
        $stmt->execute([$guessed_csv, $wrong, $new_current_idx, $new_status, $now, $winner_nickname, $room_id]);
        update_ranking($pdo, $room_id, $winner_nickname);
    } else {
        $stmt = $pdo->prepare("UPDATE rooms SET guessed_letters = ?, wrong_guesses = ?, current_player_idx = ?, turn_started_at = ? WHERE id = ?");
        $stmt->execute([$guessed_csv, $wrong, $new_current_idx, $now, $room_id]);
        $stmt = $pdo->prepare("INSERT INTO ranking (nickname, games_played, games_won, total_letters_guessed) VALUES (?, 0, 0, 1) ON CONFLICT(nickname) DO UPDATE SET total_letters_guessed = total_letters_guessed + 1");
        $stmt->execute([$player['nickname']]);
    }

    json_response(['success' => true, 'is_hit' => $is_hit]);
} elseif ($action_type === 'leave') {
    $pdo->prepare("DELETE FROM players WHERE token = ?")->execute([$token]);
    json_response(['success' => true]);
} else {
    json_response(['error' => 'unknown_action'], 400);
}
