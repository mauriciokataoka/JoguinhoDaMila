<?php
require_once __DIR__ . '/db.php';

$route = $_GET['route'] ?? '';
if (!$route) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $path = preg_replace('#^.*/api/#', '', $path);
    $path = preg_replace('#\.php$#', '', $path);
    $route = trim($path, '/');
}

$map = [
    'check-admin' => 'check_admin.php',
    'join' => 'join.php',
    'state' => 'state.php',
    'action' => 'action.php',
    'admin/state' => 'admin_state.php',
    'admin/action' => 'admin_action.php',
];

if (!isset($map[$route])) {
    json_response(['error' => 'not_found', 'route' => $route], 404);
}

require_once __DIR__ . '/endpoints/' . $map[$route];
