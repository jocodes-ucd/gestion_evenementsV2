<?php
// admin/delete_user.php
require '../includes/db.php';
require 'auth_check.php';

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$userId = (int)$_GET['id'];

// Prevent deleting own account
if ($userId == $_SESSION['user_id']) {
    header("Location: users.php?error=cannot_delete_self");
    exit;
}

// Get user info for confirmation
$stmt = $pdo->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php?error=user_not_found");
    exit;
}

// Delete user (cascading will delete notifications, but inscriptions need manual handling)
try {
    // First, delete inscriptions associated with this user's email
    $delInscriptions = $pdo->prepare("DELETE FROM inscriptions WHERE email_participant = ?");
    $delInscriptions->execute([$user['email']]);
    
    // Then delete the user (notifications will cascade delete)
    $delUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delUser->execute([$userId]);
    
    header("Location: users.php?success=user_deleted");
    exit;
    
} catch (PDOException $e) {
    header("Location: users.php?error=delete_failed");
    exit;
}
?>
