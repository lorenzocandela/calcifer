<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$payload = require_auth();
$body    = body();
$action  = $body['action'] ?? '';

match ($action) {
    'list'       => handle_list($payload),
    'mark_read'  => handle_mark_read($payload, $body),
    'delete_all' => handle_delete_all($payload),
    default      => json_response(['error' => 'Invalid action'], 400),
};

function handle_list(array $payload): never {
    $user_id = (int) $payload['sub'];
    $db      = db();

    $stmt = $db->prepare('SELECT id, type, title, body, link, image_url, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 40');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    json_response(array_map(fn($r) => [
        'id'         => (int) $r['id'],
        'type'       => $r['type'],
        'title'      => $r['title'],
        'body'       => $r['body'],
        'link'       => $r['link'],
        'image_url'  => $r['image_url'],
        'is_read'    => (bool) $r['is_read'],
        'created_at' => $r['created_at'],
    ], $rows));
}

function handle_mark_read(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $db      = db();

    if (!empty($b['all'])) {
        $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();
    } else {
        $notif_id = (int) ($b['notification_id'] ?? 0);
        if (!$notif_id) json_response(['error' => 'notification_id mancante'], 422);
        $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $notif_id, $user_id);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();
    }

    json_response(['updated' => $updated]);
}

function handle_delete_all(array $payload): never {
    $user_id = (int) $payload['sub'];
    $db = db();
    $stmt = $db->prepare('DELETE FROM notifications WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    json_response(['deleted' => $db->affected_rows]);
}