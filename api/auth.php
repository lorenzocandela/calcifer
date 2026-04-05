<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jwt.php';

$body   = body();
$action = $body['action'] ?? '';

match ($action) {
    'register' => handle_register($body),
    'login'    => handle_login($body),
    default    => json_response(['error' => 'Invalid action'], 400),
};

function handle_register(array $b): never {
    $username = trim($b['username'] ?? '');
    $email    = trim($b['email'] ?? '');
    $password = $b['password'] ?? '';

    if (strlen($username) < 3 || strlen($username) > 64) {
        json_response(['error' => 'Username deve essere tra 3 e 64 caratteri'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Email non valida'], 422);
    }
    if (strlen($password) < 8) {
        json_response(['error' => 'Password minimo 8 caratteri'], 422);
    }

    $db   = db();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        json_response(['error' => 'Email o username già in uso'], 409);
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $email, $hash);
    $stmt->execute();
    $stmt->close();

    json_response(['message' => 'Registrazione completata'], 201);
}

function handle_login(array $b): never {
    $login    = trim($b['email'] ?? $b['login'] ?? '');
    $password = $b['password'] ?? '';

    if (!$login || !$password) {
        json_response(['error' => 'Credenziali obbligatorie'], 422);
    }

    $db = db();

    $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['error' => 'Credenziali non valide'], 401);
    }

    $payload = [
        'sub'      => (int) $user['id'],
        'username' => $user['username'],
        'exp'      => time() + JWT_EXPIRY,
    ];

    json_response([
        'token' => jwt_encode($payload),
        'user'  => [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
        ],
    ]);
}