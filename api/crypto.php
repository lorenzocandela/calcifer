<?php
declare(strict_types=1);

function _crypto_key(): string {
    return hash('sha256', JWT_SECRET . '_chat', true);
}

function encrypt(string $text): string {
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($text, 'AES-256-CBC', _crypto_key(), OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}

function decrypt(string $blob): string {
    $raw = base64_decode($blob);
    $iv = substr($raw, 0, 16);
    $ciphertext = substr($raw, 16);
    $plain = openssl_decrypt($ciphertext, 'AES-256-CBC', _crypto_key(), OPENSSL_RAW_DATA, $iv);
    return $plain === false ? '' : $plain;
}