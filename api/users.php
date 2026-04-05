<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$payload = require_auth();
$body    = body();
$action  = $body['action'] ?? $_GET['action'] ?? '';

match ($action) {
    'me' => handle_me($payload),
    default => json_response(['error' => 'Invalid action'], 400),
};

function handle_me(array $payload): never {
    $db   = db();
    $id   = (int) $payload['sub'];
    $stmt = $db->prepare('SELECT id, username, email, credit_score, balance, avatar_url, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) json_response(['error' => 'User not found'], 404);

    json_response([
        'id'           => (int) $user['id'],
        'username'     => $user['username'],
        'email'        => $user['email'],
        'credit_score' => (int) $user['credit_score'],
        'balance'      => (float) $user['balance'],
        'avatar_url'   => $user['avatar_url'],
        'created_at'   => $user['created_at'],
    ]);
}