<?php
session_start();
require_once 'includes/db.php';

// Vérification connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userEmail = $_SESSION['email'];
$userId = $_SESSION['user_id'];

// Pagination
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Compter le total des événements passés
$sqlCount = "SELECT COUNT(*) as total 
             FROM inscriptions i
             JOIN evenements e ON i.evenement_id = e.id
             WHERE i.email_participant = :email
             AND e.date_evenement < NOW()";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute([':email' => $userEmail]);
$totalEvents = $stmtCount->fetch()['total'];
$totalPages = ceil($totalEvents / $perPage);

// Récupérer les événements passés avec pagination
$sqlHistory = "SELECT e.*, 
               (SELECT COUNT(*) FROM inscriptions WHERE evenement_id = e.id) as nb_inscrits
               FROM inscriptions i
               JOIN evenements e ON i.evenement_id = e.id
               WHERE i.email_participant = :email
               AND e.date_evenement < NOW()
               ORDER BY e.date_evenement DESC
               LIMIT :limit OFFSET :offset";
$stmtHistory = $pdo->prepare($sqlHistory);
$stmtHistory->bindValue(':email', $userEmail, PDO::PARAM_STR);
$stmtHistory->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmtHistory->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtHistory->execute();
$historyEvents = $stmtHistory->fetchAll();

// Statistiques générales
$sqlStats = "SELECT 
             COUNT(*) as total_participations,
             COUNT(DISTINCT YEAR(e.date_evenement)) as annees_actives
             FROM inscriptions i
             JOIN evenements e ON i.evenement_id = e.id
             WHERE i.email_participant = :email
             AND e.date_evenement < NOW()";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([':email' => $userEmail]);
$stats = $stmtStats->fetch();

// Statistiques par catégorie
$sqlCatStats = "SELECT c.nom as categorie, COUNT(*) as total
                FROM inscriptions i
                JOIN evenements e ON i.evenement_id = e.id
                LEFT JOIN categories c ON e.categorie_id = c.id
                WHERE i.email_participant = :email
                AND e.date_evenement < NOW()
                GROUP BY e.categorie_id
                ORDER BY total DESC
                LIMIT 5";
$stmtCatStats = $pdo->prepare($sqlCatStats);
$stmtCatStats->execute([':email' => $userEmail]);
$categoryStats = $stmtCatStats->fetchAll();

require_once 'includes/header.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Accueil</a></li>
            <li class="breadcrumb-item"><a href="mon_espace.php" class="text-decoration-none">Mon Espace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mon Historique</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-clock-history me-2 text-primary"></i>Mon Historique
            </h2>
            <p class="text-muted mb-0">Tous vos événements passés</p>
        </div>
        <a href="mon_espace.php" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Liste des événements -->
            <div class="card card-custom border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-calendar-check me-2"></i>Événements terminés
                        </h5>
                        <span class="badge bg-primary rounded-pill"><?= $totalEvents ?> événement(s)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($historyEvents) > 0): ?>
                        <?php foreach($historyEvents as $event): ?>
                            <?php 
                            // Calculer le taux de remplissage
                            $tauxRemplissage = $event['nb_max_participants'] > 0 
                                ? round(($event['nb_inscrits'] / $event['nb_max_participants']) * 100) 
                                : 0;
                            ?>
                            <div class="p-4 border-bottom history-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <?php if ($event['image']): ?>
                                            <img src="uploads/events/<?= htmlspecialchars($event['image']) ?>" 
                                                 alt="<?= htmlspecialchars($event['titre']) ?>"
                                                 class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="bi bi-calendar-event text-muted fs-2"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-2"><?= htmlspecialchars($event['titre']) ?></h6>
                                        <div class="d-flex flex-wrap gap-3 text-muted small">
                                            <span><i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($event['date_evenement'])) ?></span>
                                            <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($event['date_evenement'])) ?></span>
                                            <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($event['lieu']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <div class="mb-2">
                                            <span class="badge bg-secondary rounded-pill">Terminé</span>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-people me-1"></i><?= $event['nb_inscrits'] ?>/<?= $event['nb_max_participants'] ?> participants
                                        </div>
                                        <!-- Barre de remplissage -->
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar <?= $tauxRemplissage >= 80 ? 'bg-success' : ($tauxRemplissage >= 50 ? 'bg-warning' : 'bg-primary') ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $tauxRemplissage ?>%"
                                                 aria-valuenow="<?= $tauxRemplissage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= $tauxRemplissage ?>% rempli</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="p-4 bg-light">
                                <nav aria-label="Pagination historique">
                                    <ul class="pagination justify-content-center mb-0">
                                        <!-- Précédent -->
                                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Précédent">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <!-- Pages -->
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        
                                        <!-- Suivant -->
                                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Suivant">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-5 text-center">
                            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun événement dans l'historique</h5>
                            <p class="text-muted small mb-3">Vous n'avez pas encore participé à des événements passés.</p>
                            <a href="events.php" class="btn btn-primary rounded-pill">
                                <i class="bi bi-search me-2"></i>Explorer les événements
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar statistiques -->
        <div class="col-lg-4">
            <!-- Résumé -->
            <div class="card card-custom border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-bar-chart me-2"></i>Mon Résumé
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="display-6 fw-bold text-primary"><?= $stats['total_participations'] ?></div>
                                <small class="text-muted">Participations</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="display-6 fw-bold text-success"><?= $stats['annees_actives'] ?></div>
                                <small class="text-muted">Année(s) active(s)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catégories préférées -->
            <?php if (count($categoryStats) > 0): ?>
            <div class="card card-custom border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>Mes catégories
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php 
                    $colors = ['primary', 'success', 'warning', 'info', 'danger'];
                    $maxCat = $categoryStats[0]['total']; // Le max pour calculer les pourcentages relatifs
                    ?>
                    <?php foreach($categoryStats as $index => $cat): ?>
                        <?php 
                        $percentage = $maxCat > 0 ? round(($cat['total'] / $maxCat) * 100) : 0;
                        $color = $colors[$index % count($colors)];
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold"><?= htmlspecialchars($cat['categorie'] ?? 'Non catégorisé') ?></span>
                                <span class="badge bg-<?= $color ?> rounded-pill"><?= $cat['total'] ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-<?= $color ?>" 
                                     role="progressbar" 
                                     style="width: <?= $percentage ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.history-item {
    transition: background-color 0.3s ease;
}
.history-item:hover {
    background-color: #f8f9fa;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), #4361ee);
}
</style>

<?php require_once 'includes/footer.php'; ?>
