<?php
declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'calcifer');
define('DB_USER', 'lorenzo');
define('DB_PASS', '4k1r4B');
define('JWT_SECRET', '13689d96a981e5ddf4ea4e7b6e1019ba2e76fec15f0a16aae06030df547654fd');
define('JWT_EXPIRY', 3600 * 24 * 7); // 7 gg per local TODO: allagare magari

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_errno) {
            json_response(['error' => 'Database connection failed'], 503);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function json_response(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function get_bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? (function_exists('apache_request_headers') ? (apache_request_headers()['Authorization'] ?? '') : '');

    if (str_starts_with($header, 'Bearer ')) {
        return substr($header, 7);
    }
    return null;
}

function require_auth(): array {
    require_once __DIR__ . '/jwt.php';
    $token = get_bearer_token();
    if ($token === null) {
        json_response(['error' => 'Unauthorized'], 401);
    }
    $payload = jwt_decode($token);
    if ($payload === null) {
        json_response(['error' => 'Invalid or expired token'], 401);
    }
    return $payload;
}

function body(): array {
    static $parsed = null;
    if ($parsed === null) {
        $raw    = file_get_contents('php://input');
        $parsed = json_decode($raw, true) ?? [];
    }
    return $parsed;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    json_response(null, 204);
}