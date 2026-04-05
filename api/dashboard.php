<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$payload = require_auth();
handle_analytics($payload);

function handle_analytics(array $payload): never {
    $user_id = (int) $payload['sub'];
    $db = db();

    $stmt = $db->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(amount),0) AS volume FROM loans WHERE howl_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $howl = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $db->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(amount_invested),0) AS volume FROM investments WHERE calcifer_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $db->prepare('SELECT COUNT(*) AS funded FROM loans WHERE howl_id = ? AND status = \'funded\'');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $funded = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    json_response([
        'howl_stats' => [
            'total_requests' => (int)   $howl['total'],
            'total_borrowed' => (float) $howl['volume'],
            'funded' => (int)   $funded['funded'],
        ],
        'calcifer_stats' => [
            'total_investments' => (int)   $inv['total'],
            'total_invested' => (float) $inv['volume'],
        ],
    ]);
}