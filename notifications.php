<?php
// notifications.php
require 'includes/db.php';
include 'includes/header.php';

// Sécurité
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$userId = (int)$_SESSION['user_id'];

// RÉCUPÉRATION DES DONNÉES
$stmt = $pdo->prepare("SELECT n.*, e.titre AS event_titre
                       FROM notifications n
                       LEFT JOIN evenements e ON e.id = n.evenement_id
                       WHERE n.user_id = ?
                       ORDER BY n.created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Compte des non-lues
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadStmt->execute([$userId]);
$unreadCount = (int)$unreadStmt->fetchColumn();
?>

<div class="page-header">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">Vos Notifications</h1>
    </div>
</div>

<div class="container pb-5" style="margin-top: -50px; position: relative; z-index: 10;">
    
    <div class="card card-custom p-4 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Activité récente</h4>
                <p class="text-muted mb-0">Vous avez <span class="fw-bold text-primary"><?= $unreadCount ?></span> notification(s) non lue(s).</p>
            </div>

            <?php if ($unreadCount > 0): ?>
                <a href="notifications_action.php?read_all=1&redirect=notifications.php" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-check2-all me-1"></i> Tout marquer comme lu
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted opacity-25 mb-3"><i class="bi bi-bell-slash"></i></div>
            <h4 class="text-muted">Aucune notification pour le moment.</h4>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($notifications as $n): ?>
                <div class="col-12">
                    <div class="card card-custom p-4 shadow-sm <?= ($n['is_read'] ? 'opacity-75' : 'border-start border-4 border-primary') ?>">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <?php if (!$n['is_read']): ?>
                                        <span class="badge bg-primary rounded-pill">Nouveau</span>
                                    <?php endif; ?>
                                    <span class="text-muted small fw-bold">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('d/m/Y à H:i', strtotime($n['created_at'])) ?>
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <?= htmlspecialchars($n['message'] ?? '') ?>
                                </h5>

                                <?php if (!empty($n['evenement_id'])): ?>
                                    <div class="mt-2">
                                        <a class="text-decoration-none fw-bold small text-primary" href="event_details.php?id=<?= (int)$n['evenement_id'] ?>">
                                            Voir l’événement <i class="bi bi-arrow-right"></i>
                                            <?php if (!empty($n['event_titre'])): ?>
                                                <?= htmlspecialchars($n['event_titre']) ?>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!$n['is_read']): ?>
                                <a class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm border" 
                                   href="notifications_action.php?read=<?= (int)$n['id'] ?>&redirect=notifications.php">
                                    Marquer comme lu
                                </a>
                            <?php else: ?>
                                <span class="text-muted small fw-bold"><i class="bi bi-check-circle-fill text-success me-1"></i>Lu</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>