<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crypto.php';

$payload = require_auth();
$body    = body();
$action  = $body['action'] ?? '';

match ($action) {
    'fetch'     => handle_fetch($payload, $body),
    'send'      => handle_send($payload, $body),
    'mark_read' => handle_mark_read($payload, $body),
    default     => json_response(['error' => 'Invalid action'], 400),
};

function get_chat_and_check(int $chat_id, int $user_id): array {
    $db   = db();
    $stmt = $db->prepare('SELECT c.*, l.amount AS loan_amount, l.status AS loan_status, u1.username AS howl_username, u1.avatar_url AS howl_avatar, u2.username AS calcifer_username, u2.avatar_url AS calcifer_avatar FROM chats c JOIN loans l ON l.id = c.loan_id JOIN users u1 ON u1.id = c.user_howl_id JOIN users u2 ON u2.id = c.user_calcifer_id WHERE c.id = ? LIMIT 1');
    $stmt->bind_param('i', $chat_id);
    $stmt->execute();
    $chat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chat) json_response(['error' => 'Chat non trovata'], 404);

    $hid = (int) $chat['user_howl_id'];
    $cid = (int) $chat['user_calcifer_id'];
    if ($user_id !== $hid && $user_id !== $cid) {
        json_response(['error' => 'Accesso negato'], 403);
    }

    return $chat;
}

function handle_fetch(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $chat_id = (int) ($b['chat_id'] ?? 0);
    if (!$chat_id) json_response(['error' => 'chat_id mancante'], 422);

    $chat = get_chat_and_check($chat_id, $user_id);
    $db   = db();

    $other       = $user_id === (int) $chat['user_howl_id']
        ? $chat['calcifer_username']
        : $chat['howl_username'];
    $other_avatar = $user_id === (int) $chat['user_howl_id']
        ? $chat['calcifer_avatar']
        : $chat['howl_avatar'];

    $stmt = $db->prepare('
        SELECT m.id, m.sender_id, m.content, m.type, m.is_read, m.created_at,
               p.id AS p_id, p.amount AS p_amount, p.duration_months AS p_dur,
               p.interest_rate AS p_rate, p.status AS p_status, p.proposer_id AS p_proposer
        FROM messages m
        LEFT JOIN proposals p ON p.id = m.proposal_id
        WHERE m.chat_id = ?
        ORDER BY m.created_at ASC
    ');
    $stmt->bind_param('i', $chat_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $messages = array_map(function ($r) {
        $msg = [
            'id'         => (int) $r['id'],
            'sender_id'  => $r['sender_id'] ? (int) $r['sender_id'] : null,
            'content'    => decrypt($r['content']),
            'type'       => $r['type'],
            'is_read'    => (bool) $r['is_read'],
            'created_at' => $r['created_at'],
        ];
        if ($r['p_id']) {
            $msg['proposal'] = [
                'id'             => (int) $r['p_id'],
                'amount'         => (float) $r['p_amount'],
                'duration_months'=> (int) $r['p_dur'],
                'interest_rate'  => (float) $r['p_rate'],
                'status'         => $r['p_status'],
                'proposer_id'    => (int) $r['p_proposer'],
            ];
        }
        return $msg;
    }, $rows);

    json_response([
        'chat' => [
            'id'             => (int) $chat['id'],
            'loan_id'        => (int) $chat['loan_id'],
            'loan_amount'    => (float) $chat['loan_amount'],
            'loan_status'    => $chat['loan_status'],
            'user_howl_id'   => (int) $chat['user_howl_id'],
            'user_calcifer_id' => (int) $chat['user_calcifer_id'],
            'other_username' => $other,
            'other_avatar'   => $other_avatar,
            'my_id'          => $user_id,
        ],
        'messages' => $messages,
    ]);
}

function handle_send(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $chat_id = (int) ($b['chat_id'] ?? 0);
    $content = trim($b['content'] ?? '');

    if (!$chat_id || !$content) json_response(['error' => 'Parametri mancanti'], 422);

    $chat = get_chat_and_check($chat_id, $user_id);
    $db   = db();

    $enc  = encrypt($content);
    $stmt = $db->prepare('INSERT INTO messages (chat_id, sender_id, content, type) VALUES (?, ?, ?, \'text\')');
    $stmt->bind_param('iis', $chat_id, $user_id, $enc);
    $stmt->execute();
    $msg_id = $db->insert_id;
    $stmt->close();

    // notifica dest
    $other_id = $user_id === (int) $chat['user_howl_id']
        ? (int) $chat['user_calcifer_id']
        : (int) $chat['user_howl_id'];

    // user + propic mittente
    $su     = $db->query("SELECT username, avatar_url FROM users WHERE id = $user_id LIMIT 1")->fetch_assoc();
    $from   = '@' . ($su['username'] ?? 'Utente');
    $avatar = $su['avatar_url'] ?? null;
    $preview = mb_strimwidth($content, 0, 60, '…');

    $link  = "/?p=chat&id=$chat_id";
    $title = $from;
    $body  = $preview;
    $stmt  = $db->prepare('INSERT INTO notifications (user_id, type, title, body, link, image_url) VALUES (?, \'new_message\', ?, ?, ?, ?)');
    $stmt->bind_param('issss', $other_id, $title, $body, $link, $avatar);
    $stmt->execute();
    $stmt->close();

    json_response(['id' => $msg_id], 201);
}

function handle_mark_read(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $chat_id = (int) ($b['chat_id'] ?? 0);
    if (!$chat_id) json_response(['error' => 'chat_id mancante'], 422);

    get_chat_and_check($chat_id, $user_id);

    $db   = db();
    $stmt = $db->prepare('UPDATE messages SET is_read = 1 WHERE chat_id = ? AND sender_id != ?');
    $stmt->bind_param('ii', $chat_id, $user_id);
    $stmt->execute();
    $updated = $stmt->affected_rows;
    $stmt->close();

    json_response(['updated' => $updated]);
}