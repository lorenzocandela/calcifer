<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$payload = require_auth();
$body    = body();
$action  = $body['action'] ?? '';

match ($action) {
    'create' => handle_create($payload, $body),
    'feed'   => handle_feed($payload),
    'mine'   => handle_mine($payload),
    'detail' => handle_detail($payload, $body),
    default  => json_response(['error' => 'Invalid action'], 400),
};

function calc_rate(int $credit_score): float {
    return round(max(3.0, 15.0 - ($credit_score / 20.0)), 2);
}

function handle_create(array $payload, array $b): never {
    $amount          = (float) ($b['amount'] ?? 0);
    $reason          = trim($b['reason'] ?? '');
    $duration_months = (int) ($b['duration_months'] ?? 0);

    if ($amount < 100 || $amount > 50000)       json_response(['error' => 'Importo tra 100 e 50.000'], 422);
    if (strlen($reason) < 20)                    json_response(['error' => 'Motivazione minimo 20 caratteri'], 422);
    if (!in_array($duration_months, [3,6,12,18,24,36,48,60], true)) json_response(['error' => 'Durata non valida'], 422);

    $db      = db();
    $user_id = (int) $payload['sub'];

    $stmt = $db->prepare('SELECT credit_score FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $rate = calc_rate((int) $row['credit_score']);

    $stmt = $db->prepare('INSERT INTO loans (howl_id, amount, reason, interest_rate, duration_months) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('idsdi', $user_id, $amount, $reason, $rate, $duration_months);
    $stmt->execute();
    $loan_id = $db->insert_id;
    $stmt->close();

    json_response(['message' => 'Richiesta creata', 'loan_id' => $loan_id, 'interest_rate' => $rate], 201);
}

function handle_feed(array $payload): never {
    $db = db();
    $me = (int) $payload['sub'];

    $result = $db->query(
        'SELECT l.id, l.howl_id, l.amount, l.reason, l.interest_rate, l.duration_months, l.status, l.created_at,
                u.username, u.avatar_url, u.credit_score
         FROM loans l
         JOIN users u ON u.id = l.howl_id
         WHERE l.status = \'pending\'
         ORDER BY l.created_at DESC
         LIMIT 60'
    );

    $loans = [];
    while ($row = $result->fetch_assoc()) {
        $loans[] = [
            'id'              => (int) $row['id'],
            'howl_id'         => (int) $row['howl_id'],
            'amount'          => (float) $row['amount'],
            'reason'          => $row['reason'],
            'interest_rate'   => (float) $row['interest_rate'],
            'duration_months' => (int) $row['duration_months'],
            'status'          => $row['status'],
            'created_at'      => $row['created_at'],
            'username'        => $row['username'],
            'avatar_url'      => $row['avatar_url'],
            'credit_score'    => (int) $row['credit_score'],
            'is_mine'         => (int) $row['howl_id'] === $me,
        ];
    }

    json_response($loans);
}

function handle_mine(array $payload): never {
    $db      = db();
    $user_id = (int) $payload['sub'];

    $stmt = $db->prepare(
        'SELECT l.id, l.amount, l.reason, l.interest_rate, l.duration_months, l.status, l.created_at
         FROM loans l
         WHERE l.howl_id = ?
         ORDER BY l.created_at DESC'
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    json_response(array_map(fn($r) => [
        'id'              => (int)   $r['id'],
        'amount'          => (float) $r['amount'],
        'reason'          => $r['reason'],
        'interest_rate'   => (float) $r['interest_rate'],
        'duration_months' => (int)   $r['duration_months'],
        'status'          => $r['status'],
        'created_at'      => $r['created_at'],
    ], $rows));
}

function handle_detail(array $payload, array $b): never {
    $id = (int) ($b['id'] ?? 0);
    if (!$id) json_response(['error' => 'ID mancante'], 422);

    $db   = db();
    $stmt = $db->prepare(
        'SELECT l.*, u.username FROM loans l JOIN users u ON u.id = l.howl_id WHERE l.id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) json_response(['error' => 'Loan not found'], 404);

    json_response([
        'id'              => (int) $row['id'],
        'howl_id'         => (int) $row['howl_id'],
        'amount'          => (float) $row['amount'],
        'reason'          => $row['reason'],
        'interest_rate'   => (float) $row['interest_rate'],
        'duration_months' => (int) $row['duration_months'],
        'status'          => $row['status'],
        'created_at'      => $row['created_at'],
        'username'        => $row['username'],
    ]);
}