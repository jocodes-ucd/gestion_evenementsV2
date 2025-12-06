<?php
// admin/users.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// --- SEARCH & FILTER ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// --- BUILD QUERY ---
$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (u.nom LIKE ? OR u.email LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($roleFilter)) {
    $where .= " AND u.role = ?";
    $params[] = $roleFilter;
}

// --- GET USERS WITH REGISTRATION COUNT ---
$sql = "SELECT u.*, 
        COUNT(DISTINCT i.id) as total_registrations,
        COUNT(DISTINCT CASE WHEN i.date_inscription >= DATE(NOW()) - INTERVAL 30 DAY THEN i.id END) as recent_registrations
        FROM users u
        LEFT JOIN inscriptions i ON u.email = i.email_participant
        $where
        GROUP BY u.id
        ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// --- STATS ---
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalRegularUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$verifiedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();

// --- SUCCESS/ERROR MESSAGES ---
$successMsg = "";
$errorMsg = "";

if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'role_updated':
            $successMsg = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle me-2'></i>Rôle utilisateur modifié avec succès !<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'user_deleted':
            $successMsg = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle me-2'></i>Utilisateur supprimé avec succès !<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
    }
}

if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'cannot_change_own_role':
            $errorMsg = "<div class='alert alert-warning alert-dismissible fade show'><i class='bi bi-exclamation-triangle me-2'></i>Vous ne pouvez pas modifier votre propre rôle.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'cannot_delete_self':
            $errorMsg = "<div class='alert alert-warning alert-dismissible fade show'><i class='bi bi-exclamation-triangle me-2'></i>Vous ne pouvez pas supprimer votre propre compte.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'user_not_found':
            $errorMsg = "<div class='alert alert-danger alert-dismissible fade show'><i class='bi bi-x-circle me-2'></i>Utilisateur introuvable.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'delete_failed':
            $errorMsg = "<div class='alert alert-danger alert-dismissible fade show'><i class='bi bi-x-circle me-2'></i>Erreur lors de la suppression.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
    }
}
?>

<div class="container py-5">
    
    <?= $successMsg ?>
    <?= $errorMsg ?>
    
    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h2 class="fw-bold display-6 mb-0 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Gestion des Utilisateurs</h2>
            <p class="text-muted mb-0">Gérez les comptes, rôles et activités.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-dark rounded-pill fw-bold px-4">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-custom p-4 h-100 border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-primary"><?= $totalUsers ?></h3>
                        <p class="text-muted small mb-0 fw-bold">Total Utilisateurs</p>
                    </div>
                    <div class="fs-1 text-primary opacity-25"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-warning"><?= $totalAdmins ?></h3>
                        <p class="text-muted small mb-0 fw-bold">Administrateurs</p>
                    </div>
                    <div class="fs-1 text-warning opacity-25"><i class="bi bi-shield-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 h-100 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-info"><?= $totalRegularUsers ?></h3>
                        <p class="text-muted small mb-0 fw-bold">Utilisateurs Standard</p>
                    </div>
                    <div class="fs-1 text-info opacity-25"><i class="bi bi-person"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-4 h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-success"><?= $verifiedUsers ?></h3>
                        <p class="text-muted small mb-0 fw-bold">Comptes Vérifiés</p>
                    </div>
                    <div class="fs-1 text-success opacity-25"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="card card-custom p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">RECHERCHER</label>
                <input type="text" name="search" class="form-control" placeholder="Nom ou email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">RÔLE</label>
                <select name="role" class="form-select">
                    <option value="">Tous</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                    <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Rechercher</button>
            </div>
        </form>
        <?php if(!empty($search) || !empty($roleFilter)): ?>
            <div class="mt-3">
                <a href="users.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Réinitialiser les filtres</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- USERS TABLE -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-primary"></i>Liste des Utilisateurs</h5>
            <span class="badge bg-light text-dark border"><?= count($users) ?> résultats</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4 text-muted small text-uppercase fw-bold">Utilisateur</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Email</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Rôle</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Statut</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Inscriptions</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Membre depuis</th>
                            <th class="py-3 text-end pe-4 text-muted small text-uppercase fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($users) > 0): ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 45px; height: 45px; flex-shrink:0;">
                                                <?php 
                                                    if(!empty($user['photo_profil'])) {
                                                        $avatarSrc = '/gestion_evenements/' . $user['photo_profil'];
                                                    } else {
                                                        $avatarSrc = 'https://ui-avatars.com/api/?name='.urlencode($user['nom']).'&background=random';
                                                    }
                                                ?>
                                                <img src="<?= htmlspecialchars($avatarSrc) ?>" 
                                                     class="rounded-circle shadow-sm w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($user['nom']) ?>
                                                </div>
                                                <span class="small text-muted">ID: <?= $user['id'] ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </a>
                                    </td>

                                    <td>
                                        <?php if($user['role'] === 'admin'): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold">
                                                <i class="bi bi-shield-check me-1"></i>Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-25 text-info border border-info">
                                                <i class="bi bi-person me-1"></i>User
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($user['is_verified'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">
                                                <i class="bi bi-check-circle-fill me-1"></i>Vérifié
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                <i class="bi bi-clock me-1"></i>En attente
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-primary"><?= $user['total_registrations'] ?> total</span>
                                            <span class="small text-muted"><?= $user['recent_registrations'] ?> ce mois</span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="small"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm border" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3">
                                                <li><h6 class="dropdown-header small text-uppercase">Gérer</h6></li>
                                                <li><a class="dropdown-item" href="user_details.php?id=<?= $user['id'] ?>"><i class="bi bi-eye me-2 text-info"></i>Voir détails</a></li>
                                                <li><a class="dropdown-item" href="edit_user_role.php?id=<?= $user['id'] ?>"><i class="bi bi-pencil me-2 text-warning"></i>Modifier le rôle</a></li>
                                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ? Toutes ses inscriptions seront également supprimées.');"><i class="bi bi-trash me-2"></i>Supprimer</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
