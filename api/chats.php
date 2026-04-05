<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crypto.php';

$payload = require_auth();
$body = body();
$action  = $body['action'] ?? '';

match ($action) {
    'create' => handle_create($payload, $body),
    'list' => handle_list($payload),
    'delete' => handle_delete($payload, $body),
    default  => json_response(['error' => 'Invalid action'], 400),
};

function handle_create(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $loan_id = (int) ($b['loan_id'] ?? 0);
    $amount = (float) ($b['amount'] ?? 0);
    $duration = (int) ($b['duration_months'] ?? 0);
    $rate = (float) ($b['interest_rate'] ?? 0);

    if (!$loan_id || !$amount || !$duration || !$rate) {
        json_response(['error' => 'Parametri mancanti'], 422);
    }

    $db = db();

    $stmt = $db->prepare('SELECT id, howl_id, status FROM loans WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $loan_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$loan) json_response(['error' => 'Richiesta non trovata'], 404);
    if ($loan['status'] !== 'pending') json_response(['error' => 'Richiesta non più disponibile'], 409);
    if ((int) $loan['howl_id'] === $user_id) json_response(['error' => 'Non puoi proporre sul tuo prestito'], 403);

    $howl_id = (int) $loan['howl_id'];

    // chat
    $db->query("INSERT IGNORE INTO chats (user_howl_id, user_calcifer_id, loan_id) VALUES ($howl_id, $user_id, $loan_id)");
    $stmt = $db->prepare('SELECT id FROM chats WHERE user_howl_id = ? AND user_calcifer_id = ? AND loan_id = ? LIMIT 1');
    $stmt->bind_param('iii', $howl_id, $user_id, $loan_id);
    $stmt->execute();
    $chat = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $chat_id = (int) $chat['id'];

    // proposta
    $stmt = $db->prepare('INSERT INTO proposals (chat_id, loan_id, proposer_id, amount, duration_months, interest_rate) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiidid', $chat_id, $loan_id, $user_id, $amount, $duration, $rate);
    $stmt->execute();
    $proposal_id = $db->insert_id;
    $stmt->close();

    // proposta
    $content = encrypt("Nuova proposta inviata");
    $stmt = $db->prepare('INSERT INTO messages (chat_id, sender_id, content, type, proposal_id) VALUES (?, ?, ?, \'proposal\', ?)');
    $stmt->bind_param('iisi', $chat_id, $user_id, $content, $proposal_id);
    $stmt->execute();
    $stmt->close();

    // notifica howl
    $cu    = $db->query("SELECT username FROM users WHERE id = $user_id LIMIT 1")->fetch_assoc();
    $cname = '@' . ($cu['username'] ?? 'Utente');
    $afmt  = '€' . number_format($amount, 2, ',', '.');
    $pbody = "$cname propone $afmt · $duration mesi";
    $link  = "/?p=chat&id=$chat_id";
    $stmt  = $db->prepare('INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, \'new_proposal\', \'Nuova proposta\', ?, ?)');
    $stmt->bind_param('iss', $howl_id, $pbody, $link);
    $stmt->execute();
    $stmt->close();

    json_response(['chat_id' => $chat_id, 'proposal_id' => $proposal_id], 201);
}

function handle_list(array $payload): never {
    $user_id = (int) $payload['sub'];
    $db      = db();

    $result = $db->query("
        SELECT
            c.id, c.user_howl_id, c.user_calcifer_id, c.loan_id,
            l.amount AS loan_amount,
            CASE WHEN c.user_howl_id = $user_id THEN u2.username ELSE u1.username END AS other_username,
            CASE WHEN c.user_howl_id = $user_id THEN u2.avatar_url ELSE u1.avatar_url END AS other_avatar,
            (SELECT content FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_msg_enc,
            (SELECT created_at FROM messages WHERE chat_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_msg_at,
            (SELECT COUNT(*) FROM messages WHERE chat_id = c.id AND sender_id != $user_id AND is_read = 0) AS unread
        FROM chats c
        JOIN users u1 ON u1.id = c.user_howl_id
        JOIN users u2 ON u2.id = c.user_calcifer_id
        JOIN loans l  ON l.id  = c.loan_id
        WHERE (c.user_howl_id = $user_id OR c.user_calcifer_id = $user_id)
        ORDER BY last_msg_at DESC
    ");

    $chats = [];
    while ($row = $result->fetch_assoc()) {
        $preview = $row['last_msg_enc'] ? decrypt($row['last_msg_enc']) : '';
        $chats[] = [
            'id'             => (int) $row['id'],
            'loan_id'        => (int) $row['loan_id'],
            'loan_amount'    => (float) $row['loan_amount'],
            'other_username' => $row['other_username'],
            'other_avatar'   => $row['other_avatar'],
            'last_message'   => mb_strimwidth($preview, 0, 60, '…'),
            'last_msg_at'    => $row['last_msg_at'],
            'unread'         => (int) $row['unread'],
        ];
    }

    json_response($chats);
}

function handle_delete(array $payload, array $b): never {
    $user_id = (int) $payload['sub'];
    $chat_id = (int) ($b['chat_id'] ?? 0);
    if (!$chat_id) json_response(['error' => 'chat_id mancante'], 422);

    $db   = db();
    $stmt = $db->prepare('SELECT id, user_howl_id, user_calcifer_id FROM chats WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $chat_id);
    $stmt->execute();
    $chat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chat) json_response(['error' => 'Chat non trovata'], 404);

    if ((int) $chat['user_howl_id'] === $user_id) {
        $db->query("UPDATE chats SET deleted_by_howl = 1 WHERE id = $chat_id");
    } elseif ((int) $chat['user_calcifer_id'] === $user_id) {
        $db->query("UPDATE chats SET deleted_by_calcifer = 1 WHERE id = $chat_id");
    } else {
        json_response(['error' => 'Accesso negato'], 403);
    }

    json_response(['deleted' => true]);
}