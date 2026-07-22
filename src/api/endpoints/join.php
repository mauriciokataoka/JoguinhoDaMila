<?php
require_method('POST');
$body = get_json_body();
$nickname = trim($body['nickname'] ?? '');
$provided_token = trim($body['token'] ?? '');

if ($nickname === '' || mb_strlen($nickname) > 30) {
    json_response(['error' => 'nickname_invalid'], 400);
}

$pdo = get_db();

if ($provided_token !== '') {
    $stmt = $pdo->prepare("SELECT 1 FROM bans WHERE token = ?");
    $stmt->execute([$provided_token]);
    if ($stmt->fetchColumn()) {
        json_response(['error' => 'banned'], 403);
    }
}

$stmt = $pdo->query("SELECT id, status FROM rooms ORDER BY id DESC LIMIT 1");
$room = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$room) {
    json_response(['error' => 'no_rooms'], 503);
}

if (in_array($room['status'], ['em_jogo', 'encerrada'], true)) {
    json_response(['error' => 'room_unavailable', 'status' => $room['status']], 409);
}

$token = $provided_token !== '' ? $provided_token : generate_token();

$stmt = $pdo->prepare("SELECT room_id FROM players WHERE token = ?");
$stmt->execute([$token]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing && (int)$existing['room_id'] === (int)$room['id']) {
    $stmt = $pdo->prepare("UPDATE players SET nickname = ? WHERE token = ?");
    $stmt->execute([$nickname, $token]);
} else {
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), -1) + 1 FROM players WHERE room_id = ?");
    $stmt->execute([$room['id']]);
    $position = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO players (token, nickname, room_id, position) VALUES (?, ?, ?, ?)");
    $stmt->execute([$token, $nickname, $room['id'], $position]);
}

$state = build_client_state($pdo, (int)$room['id'], $token);
json_response([
    'token' => $token,
    'room_id' => (int)$room['id'],
    'state' => $state,
]);
