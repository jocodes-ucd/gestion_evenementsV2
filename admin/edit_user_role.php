<?php
// admin/edit_user_role.php
require '../includes/db.php';
require 'auth_check.php';

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$userId = (int)$_GET['id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php?error=user_not_found");
    exit;
}

// Prevent changing own role
if ($userId == $_SESSION['user_id']) {
    header("Location: users.php?error=cannot_change_own_role");
    exit;
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRole = $_POST['role'];
    
    if (in_array($newRole, ['admin', 'user'])) {
        $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($updateStmt->execute([$newRole, $userId])) {
            header("Location: users.php?success=role_updated");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Rôle invalide.</div>";
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            <a href="users.php" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </a>
            
            <div class="card card-custom p-5">
                <div class="text-center mb-4">
                    <?php 
                        if(!empty($user['photo_profil'])) {
                            $avatarSrc = '/gestion_evenements/' . $user['photo_profil'];
                        } else {
                            $avatarSrc = 'https://ui-avatars.com/api/?name='.urlencode($user['nom']).'&background=random&size=100';
                        }
                    ?>
                    <img src="<?= htmlspecialchars($avatarSrc) ?>" 
                         class="rounded-circle shadow mb-3" 
                         style="width: 80px; height: 80px; object-fit: cover;">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['nom']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                
                <hr>
                
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-shield-check me-2 text-primary"></i>Modifier le rôle
                </h5>
                
                <?= $message ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">RÔLE ACTUEL</label>
                        <div class="p-3 bg-light rounded">
                            <?php if($user['role'] === 'admin'): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-shield-check me-1"></i>Administrateur
                                </span>
                            <?php else: ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-person me-1"></i>Utilisateur
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">NOUVEAU RÔLE</label>
                        <select name="role" class="form-select form-select-lg" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>
                                👤 Utilisateur Standard
                            </option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                                👑 Administrateur
                            </option>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Les administrateurs ont accès au panneau de gestion complet.
                        </small>
                    </div>
                    
                    <div class="alert alert-warning border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Attention :</strong> Modifier le rôle changera immédiatement les permissions de cet utilisateur.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold flex-grow-1">
                            <i class="bi bi-check-circle me-2"></i>Confirmer la modification
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
