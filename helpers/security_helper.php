<?php
// helpers/security_helper.php

/**
 * Mencegah XSS (Cross-Site Scripting) dengan membersihkan output HTML.
 * Digunakan saat mencetak data dari database ke tampilan (view).
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Membuat atau mengambil CSRF Token yang tersimpan di dalam session.
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Menghasilkan elemen input hidden berisi CSRF Token untuk disisipkan di dalam form HTML.
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

/**
 * Memverifikasi validitas CSRF Token yang dikirimkan melalui form POST.
 */
if (!function_exists('verify_csrf_token')) {
    function csrf_verify($token) {
        $token = $_POST['csrf_token'] ?? '';
        if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Permintaan ditolak: token CSRF tidak valid atau kedaluwarsa.');
        }
    }
}