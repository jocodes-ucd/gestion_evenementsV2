<?php
// notifications_action.php
require 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Sécurité
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$userId = (int)$_SESSION['user_id'];

// --- LOGIQUE DE REDIRECTION INTELLIGENTE ---
// On regarde si l'URL contient ?redirect=... sinon on va vers l'accueil
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
// -------------------------------------------

try {
    // 1. Marquer UNE notification comme lue
    if (isset($_GET['read'])) {
        $nid = (int)$_GET['read'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$nid, $userId]);
    }

    // 2. Marquer TOUT comme lu
    if (isset($_GET['read_all'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

} catch (PDOException $e) {
    die("Erreur SQL");
}

// Redirection vers la page d'où l'on vient
header("Location: " . $redirect);
exit;
?>