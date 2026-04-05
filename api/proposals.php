<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crypto.php';

$payload = require_auth();
$body    = body();
$action  = $body['action'] ?? '';

match ($action) {
    'accept' => handle_accept($payload, $body),
    'reject' => handle_reject($payload, $body),
    default  => json_response(['error' => 'Invalid action'], 400),
};

function handle_accept(array $payload, array $b): never {
    $user_id     = (int) $payload['sub'];
    $proposal_id = (int) ($b['proposal_id'] ?? 0);
    if (!$proposal_id) json_response(['error' => 'proposal_id mancante'], 422);

    $db = db();

    $stmt = $db->prepare('SELECT p.*, c.user_howl_id, c.user_calcifer_id, c.loan_id FROM proposals p JOIN chats c ON c.id = p.chat_id WHERE p.id = ? LIMIT 1');
    $stmt->bind_param('i', $proposal_id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$p) json_response(['error' => 'Proposta non trovata'], 404);
    if ($p['status'] !== 'pending') json_response(['error' => 'Proposta non più in attesa'], 409);
    if ((int) $p['user_howl_id'] !== $user_id) json_response(['error' => 'Solo Howl può accettare'], 403);

    $howl_id     = (int) $p['user_howl_id'];
    $calcifer_id = (int) $p['user_calcifer_id'];
    $loan_id     = (int) $p['loan_id'];
    $amount      = (float) $p['amount'];
    $chat_id     = (int) $p['chat_id'];

    try {
        $db->begin_transaction();

        // 1. proposta ok
        $db->query("UPDATE proposals SET status = 'accepted' WHERE id = $proposal_id");

        // 2. loan funded
        $db->query("UPDATE loans SET status = 'funded' WHERE id = $loan_id");

        // 3. investmenb
        $stmt = $db->prepare('INSERT INTO investments (calcifer_id, loan_id, amount_invested) VALUES (?, ?, ?)');
        $stmt->bind_param('iid', $calcifer_id, $loan_id, $amount);
        $stmt->execute();
        $stmt->close();

        // 4. balance howl +amount, calcifer -amount
        $db->query("UPDATE users SET balance = balance + $amount WHERE id = $howl_id");
        $db->query("UPDATE users SET balance = balance - $amount WHERE id = $calcifer_id");

        // 5. transazione
        $stmt = $db->prepare('INSERT INTO transactions (user_id, type, amount) VALUES (?, ?, ?)');
        $type_r = 'repayment'; $type_i = 'investment';
        $stmt->bind_param('isd', $howl_id, $type_r, $amount);
        $stmt->execute();
        $stmt->bind_param('isd', $calcifer_id, $type_i, $amount);
        $stmt->execute();
        $stmt->close();

        // 6. system msg
        $sys_text = encrypt("Finanziamento completato. €" . number_format($amount, 2, ',', '.') . " trasferiti.");
        $stmt = $db->prepare('INSERT INTO messages (chat_id, sender_id, content, type) VALUES (?, NULL, ?, \'system\')');
        $stmt->bind_param('is', $chat_id, $sys_text);
        $stmt->execute();
        $stmt->close();

        // 7. notifica con chi ha accettato + importo
        $link  = "/?p=chat&id=$chat_id";
        $hu    = $db->query("SELECT username FROM users WHERE id = $howl_id LIMIT 1")->fetch_assoc();
        $hname = '@' . ($hu['username'] ?? 'Utente');
        $amt   = '€' . number_format($amount, 2, ',', '.');
        $nbody = "$hname ha accettato · $amt trasferiti";
        $stmt  = $db->prepare('INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, \'proposal_accepted\', \'Proposta accettata\', ?, ?)');
        $stmt->bind_param('iss', $calcifer_id, $nbody, $link);
        $stmt->execute();
        $stmt->close();

        $db->commit();
    } catch (\Throwable $e) {
        $db->rollback();
        json_response(['error' => 'Errore durante la transazione'], 500);
    }

    json_response(['message' => 'Finanziamento completato']);
}

function handle_reject(array $payload, array $b): never {
    $user_id     = (int) $payload['sub'];
    $proposal_id = (int) ($b['proposal_id'] ?? 0);
    if (!$proposal_id) json_response(['error' => 'proposal_id mancante'], 422);

    $db = db();

    $stmt = $db->prepare('SELECT p.*, c.user_howl_id, c.user_calcifer_id FROM proposals p JOIN chats c ON c.id = p.chat_id WHERE p.id = ? LIMIT 1');
    $stmt->bind_param('i', $proposal_id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$p) json_response(['error' => 'Proposta non trovata'], 404);
    if ($p['status'] !== 'pending') json_response(['error' => 'Proposta non più in attesa'], 409);
    if ((int) $p['user_howl_id'] !== $user_id) json_response(['error' => 'Solo Howl può rifiutare'], 403);

    $calcifer_id = (int) $p['user_calcifer_id'];
    $chat_id     = (int) $p['chat_id'];

    $db->query("UPDATE proposals SET status = 'rejected' WHERE id = $proposal_id");

    $sys_text = encrypt("Proposta rifiutata.");
    $stmt = $db->prepare('INSERT INTO messages (chat_id, sender_id, content, type) VALUES (?, NULL, ?, \'system\')');
    $stmt->bind_param('is', $chat_id, $sys_text);
    $stmt->execute();
    $stmt->close();

    $link  = "/?p=chat&id=$chat_id";
    $hu2   = $db->query("SELECT username FROM users WHERE id = {$p['user_howl_id']} LIMIT 1")->fetch_assoc();
    $hname2 = '@' . ($hu2['username'] ?? 'Utente');
    $rbody  = "$hname2 ha rifiutato la proposta";
    $stmt   = $db->prepare('INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, \'proposal_rejected\', \'Proposta rifiutata\', ?, ?)');
    $stmt->bind_param('iss', $calcifer_id, $rbody, $link);
    $stmt->execute();
    $stmt->close();

    json_response(['message' => 'Proposta rifiutata']);
}