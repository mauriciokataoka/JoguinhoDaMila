<?php
require_method('GET');
$token = $_GET['token'] ?? '';
$valid = is_string($token) && $token !== '' && hash_equals($GLOBALS['ADMIN_TOKEN'], $token);
json_response([
    'valid' => $valid,
    'admin_name' => $GLOBALS['ADMIN_NAME'],
]);
