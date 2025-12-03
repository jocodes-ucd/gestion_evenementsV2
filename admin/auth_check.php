<?php
// /admin/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SI l'utilisateur n'est pas connecté OU qu'il n'est pas admin...
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // ... on l'éjecte vers le login
    header("Location: ../login.php");
    exit;
}
?>