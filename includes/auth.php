<?php
// includes/auth.php
function require_login(string $redirect = "/pacita1_reservation/login.php"): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header("Location: $redirect");
        exit();
    }
}

function require_admin(string $redirect = "/pacita1_reservation/dashboard.php"): void {
    require_login("/pacita1_reservation/login.php");
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: $redirect");
        exit();
    }
}