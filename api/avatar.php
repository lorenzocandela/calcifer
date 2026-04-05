<?php
declare(strict_types=1);

// error return json
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => "PHP Error [$errno]: $errstr in $errfile:$errline"]);
    exit;
});
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit;
});

require_once __DIR__ . '/config.php';

$payload = require_auth();
$user_id = (int) $payload['sub'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Metodo non consentito'], 405);
}

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['avatar']['error'] ?? -1;
    json_response(['error' => "Errore upload (code: $code)"], 422);
}

$file = $_FILES['avatar'];

if ($file['size'] > 8 * 1024 * 1024) {
    json_response(['error' => 'File troppo grande (max 8MB)'], 422);
}

$info = @getimagesize($file['tmp_name']);
if (!$info) {
    json_response(['error' => 'File non è un\'immagine valida'], 422);
}

$mime    = $info['mime'];
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($mime, $allowed, true)) {
    json_response(['error' => "Formato non supportato: $mime"], 422);
}

$dir = __DIR__ . '/../public/assets/avatars/';

if (!is_dir($dir)) {
    $created = mkdir($dir, 0755, true);
    if (!$created) {
        json_response(['error' => "Impossibile creare directory: $dir — controllare permessi"], 500);
    }
}

if (!is_writable($dir)) {
    json_response(['error' => "Directory non scrivibile: $dir"], 500);
}

$dest    = $dir . $user_id . '.jpg';
$rel_url = '/assets/avatars/' . $user_id . '.jpg';
$saved   = false;

// save file propic
$saved = false;
if (extension_loaded('gd') && function_exists('imagecreatefromjpeg')) {
    $src = null;
    if ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($file['tmp_name']);
    elseif ($mime === 'image/png')  $src = @imagecreatefrompng($file['tmp_name']);
    elseif ($mime === 'image/gif')  $src = @imagecreatefromgif($file['tmp_name']);
    elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp'))
        $src = @imagecreatefromwebp($file['tmp_name']);

    if ($src) {
        $sw = imagesx($src);
        $sh = imagesy($src);

        if ($mime === 'image/png') {
            $bg = imagecreatetruecolor($sw, $sh);
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagecopy($bg, $src, 0, 0, 0, 0, $sw, $sh);
            imagedestroy($src);
            $src = $bg;
        }

        $size = min($sw, $sh);
        $sx   = (int)(($sw - $size) / 2);
        $sy   = (int)(($sh - $size) / 2);
        $out  = imagecreatetruecolor(400, 400);
        imagecopyresampled($out, $src, 0, 0, $sx, $sy, 400, 400, $size, $size);
        imagedestroy($src);
        $saved = imagejpeg($out, $dest, 88);
        imagedestroy($out);
    }
}

// fallback no gd TODO: fixare su server ver gd
if (!$saved) {
    $saved = move_uploaded_file($file['tmp_name'], $dest);
}

if (!$saved || !file_exists($dest)) {
    json_response(['error' => 'Salvataggio fallito — GD: ' . (extension_loaded('gd') ? 'sì' : 'no')], 500);
}

$db   = db();
$stmt = $db->prepare('UPDATE users SET avatar_url = ? WHERE id = ?');
$stmt->bind_param('si', $rel_url, $user_id);
$stmt->execute();
$stmt->close();

json_response(['url' => $rel_url . '?v=' . time()]);