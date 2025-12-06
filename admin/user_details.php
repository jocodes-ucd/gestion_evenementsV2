<?php
// admin/user_details.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

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
    die("<div class='container mt-5 alert alert-danger'>Utilisateur introuvable.</div>");
}

// Get user's registrations
$regStmt = $pdo->prepare("
    SELECT i.*, e.titre as event_titre, e.date_evenement 
    FROM inscriptions i
    LEFT JOIN evenements e ON i.evenement_id = e.id
    WHERE i.email_participant = ?
    ORDER BY i.date_inscription DESC
");
$regStmt->execute([$user['email']]);
$registrations = $regStmt->fetchAll();

// Get user's notifications
$notifStmt = $pdo->prepare("
    SELECT n.*, e.titre as event_titre
    FROM notifications n
    LEFT JOIN evenements e ON n.evenement_id = e.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 10
");
$notifStmt->execute([$userId]);
$notifications = $notifStmt->fetchAll();
?>

<div class="container py-5">
    
    <!-- HEADER -->
    <div class="mb-4">
        <a href="users.php" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i>Retour à la liste
        </a>
        <h2 class="fw-bold display-6 mb-0 text-dark">
            <i class="bi bi-person-circle me-2 text-primary"></i>Détails de l'utilisateur
        </h2>
    </div>

    <div class="row g-4">
        
        <!-- LEFT COLUMN: USER INFO -->
        <div class="col-lg-4">
            
            <!-- PROFILE CARD -->
            <div class="card card-custom p-4 text-center mb-4">
                <?php 
                    if(!empty($user['photo_profil'])) {
                        $avatarSrc = '/gestion_evenements/' . $user['photo_profil'];
                    } else {
                        $avatarSrc = 'https://ui-avatars.com/api/?name='.urlencode($user['nom']).'&background=random&size=200';
                    }
                ?>
                <img src="<?= htmlspecialchars($avatarSrc) ?>" 
                     class="rounded-circle shadow mx-auto mb-3" 
                     style="width: 120px; height: 120px; object-fit: cover;">
                
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['nom']) ?></h4>
                <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                
                <?php if($user['role'] === 'admin'): ?>
                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold mb-3">
                        <i class="bi bi-shield-check me-1"></i>Administrateur
                    </span>
                <?php else: ?>
                    <span class="badge bg-info bg-opacity-25 text-info border border-info mb-3">
                        <i class="bi bi-person me-1"></i>Utilisateur
                    </span>
                <?php endif; ?>
                
                <hr>
                
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h5 class="fw-bold text-primary mb-0"><?= count($registrations) ?></h5>
                        <small class="text-muted">Inscriptions</small>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-0"><?= count($notifications) ?></h5>
                        <small class="text-muted">Notifications</small>
                    </div>
                </div>
            </div>
            
            <!-- INFO CARD -->
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3 text-uppercase text-muted small">Informations</h6>
                
                <div class="mb-3">
                    <small class="text-muted d-block">ID Utilisateur</small>
                    <strong>#<?= $user['id'] ?></strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Statut du compte</small>
                    <?php if($user['is_verified'] == 1): ?>
                        <span class="badge bg-success bg-opacity-25 text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>Vérifié
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary bg-opacity-25 text-secondary">
                            <i class="bi bi-clock me-1"></i>En attente de vérification
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Date d'inscription</small>
                    <strong><?= date('d/m/Y à H:i', strtotime($user['created_at'])) ?></strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Rôle</small>
                    <strong><?= $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur Standard' ?></strong>
                </div>
            </div>
        </div>
        
        <!-- RIGHT COLUMN: ACTIVITY -->
        <div class="col-lg-8">
            
            <!-- REGISTRATIONS -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-calendar-check me-2 text-primary"></i>Inscriptions aux événements
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if(count($registrations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4">Événement</th>
                                        <th class="py-3">Date de l'événement</th>
                                        <th class="py-3">Inscrit le</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($registrations as $reg): ?>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($reg['event_titre'] ?? 'Événement supprimé') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($reg['date_evenement']): ?>
                                                    <span class="small">
                                                        <i class="bi bi-calendar-event me-1 text-primary"></i>
                                                        <?= date('d/m/Y H:i', strtotime($reg['date_evenement'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="small text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($reg['date_inscription'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Aucune inscription pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- NOTIFICATIONS -->
            <div class="card card-custom">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-bell me-2 text-primary"></i>Notifications récentes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if(count($notifications) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($notifications as $notif): ?>
                                <div class="list-group-item p-3 <?= $notif['is_read'] ? '' : 'bg-light' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($notif['message']) ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('d/m/Y à H:i', strtotime($notif['created_at'])) ?>
                                            </small>
                                        </div>
                                        <?php if(!$notif['is_read']): ?>
                                            <span class="badge bg-primary">Non lu</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Aucune notification.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
