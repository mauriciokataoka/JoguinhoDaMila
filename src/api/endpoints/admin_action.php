<?php
require_method('POST');
$body = get_json_body();
$token = trim($body['token'] ?? '');
$action_type = trim($body['type'] ?? '');
$payload = $body['payload'] ?? [];

if (!hash_equals($GLOBALS['ADMIN_TOKEN'], $token)) {
    json_response(['error' => 'unauthorized'], 401);
}

$pdo = get_db();

if ($action_type === 'create_room') {
    $new_id = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM rooms")->fetchColumn();
    $pdo->prepare("INSERT INTO rooms (id) VALUES (?)")->execute([$new_id]);
    json_response(['success' => true, 'room_id' => $new_id]);
} elseif ($action_type === 'start_game') {
    $room_id = (int)($payload['room'] ?? 0);
    $word = trim($payload['word'] ?? '');
    $max_attempts = (int)($payload['max_attempts'] ?? 6);
    if ($room_id <= 0 || $word === '' || $max_attempts < 1 || $max_attempts > 26) {
        json_response(['error' => 'invalid_params'], 400);
    }
    $stmt = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $status = $stmt->fetchColumn();
    if ($status !== 'aguardando') {
        json_response(['error' => 'room_not_aguardando', 'status' => $status], 400);
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM players WHERE room_id = ?");
    $stmt->execute([$room_id]);
    if ((int)$stmt->fetchColumn() < 1) {
        json_response(['error' => 'no_players'], 400);
    }
    $now = date('Y-m-d H:i:s');
    $pdo->prepare("UPDATE rooms SET word = ?, max_attempts = ?, status = 'em_jogo', current_player_idx = 0, turn_started_at = ?, started_at = ?, guessed_letters = '', wrong_guesses = 0 WHERE id = ?")
        ->execute([$word, $max_attempts, $now, $now, $room_id]);
    json_response(['success' => true]);
} elseif ($action_type === 'end_game') {
    $room_id = (int)($payload['room'] ?? 0);
    if ($room_id <= 0) json_response(['error' => 'invalid_params'], 400);
    $pdo->prepare("UPDATE rooms SET status = 'encerrada', turn_started_at = NULL, ended_at = ? WHERE id = ?")
        ->execute([date('Y-m-d H:i:s'), $room_id]);
    json_response(['success' => true]);
} elseif ($action_type === 'reset_game') {
    $room_id = (int)($payload['room'] ?? 0);
    if ($room_id <= 0) json_response(['error' => 'invalid_params'], 400);
    $pdo->prepare("DELETE FROM players WHERE room_id = ?")->execute([$room_id]);
    $pdo->prepare("UPDATE rooms SET status = 'aguardando', word = NULL, max_attempts = NULL, guessed_letters = '', wrong_guesses = 0, current_player_idx = 0, turn_started_at = NULL, started_at = NULL, ended_at = NULL, winner_nickname = NULL WHERE id = ?")
        ->execute([$room_id]);
    json_response(['success' => true]);
} elseif ($action_type === 'move_client') {
    $client_token = trim($payload['client_token'] ?? '');
    $to_room = (int)($payload['to_room'] ?? 0);
    if ($client_token === '' || $to_room <= 0) json_response(['error' => 'invalid_params'], 400);
    $stmt = $pdo->prepare("SELECT id, status FROM rooms WHERE id = ?");
    $stmt->execute([$to_room]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) json_response(['error' => 'target_room_not_found'], 404);
    if ($target['status'] !== 'aguardando') json_response(['error' => 'target_not_aguardando', 'status' => $target['status']], 400);
    $position = (int)$pdo->prepare("SELECT COALESCE(MAX(position), -1) + 1 FROM players WHERE room_id = ?");
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), -1) + 1 FROM players WHERE room_id = ?");
    $stmt->execute([$to_room]);
    $position = (int)$stmt->fetchColumn();
    $pdo->prepare("UPDATE players SET room_id = ?, position = ? WHERE token = ?")
        ->execute([$to_room, $position, $client_token]);
    json_response(['success' => true]);
} elseif ($action_type === 'ban') {
    $client_token = trim($payload['client_token'] ?? '');
    if ($client_token === '') json_response(['error' => 'invalid_params'], 400);
    $stmt = $pdo->prepare("SELECT nickname FROM players WHERE token = ?");
    $stmt->execute([$client_token]);
    $nickname = $stmt->fetchColumn() ?: 'desconhecido';
    $pdo->prepare("INSERT OR REPLACE INTO bans (token, nickname) VALUES (?, ?)")
        ->execute([$client_token, $nickname]);
    $pdo->prepare("DELETE FROM players WHERE token = ?")->execute([$client_token]);
    json_response(['success' => true]);
} elseif ($action_type === 'unban') {
    $client_token = trim($payload['client_token'] ?? '');
    if ($client_token === '') json_response(['error' => 'invalid_params'], 400);
    $pdo->prepare("DELETE FROM bans WHERE token = ?")->execute([$client_token]);
    json_response(['success' => true]);
} else {
    json_response(['error' => 'unknown_action'], 400);
}
