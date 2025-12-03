<?php
// /admin/delete_event.php
require '../includes/db.php';
require 'auth_check.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Suppression (Grâce à ON DELETE CASCADE dans la BDD, les inscriptions sont aussi supprimées automatiquement)
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
    $stmt->execute([$id]);
}

// Retour au tableau de bord
header("Location: index.php?msg=deleted");
exit;
?>