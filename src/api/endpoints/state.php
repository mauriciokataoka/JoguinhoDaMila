<?php
require_method('GET');
$room_id = (int)($_GET['room'] ?? 0);
$token = trim($_GET['token'] ?? '');

if ($room_id <= 0 || $token === '') {
    json_response(['error' => 'invalid_params'], 400);
}

$pdo = get_db();

$stmt = $pdo->prepare("SELECT 1 FROM bans WHERE token = ?");
$stmt->execute([$token]);
if ($stmt->fetchColumn()) {
    json_response(['error' => 'banned'], 403);
}

$state = build_client_state($pdo, $room_id, $token);
if ($state === null) {
    json_response(['error' => 'not_in_room'], 403);
}
json_response($state);
