<?php
// mon_espace.php - Tableau de bord utilisateur
session_start();
require 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'];

// 1. Récupérer les événements inscrits (à venir)
$sqlUpcoming = "SELECT e.*, c.nom as categorie_nom, i.date_inscription, i.id as inscription_id
                FROM inscriptions i
                INNER JOIN evenements e ON i.evenement_id = e.id
                LEFT JOIN categories c ON e.categorie_id = c.id
                WHERE i.email_participant = ? AND e.date_evenement >= NOW()
                ORDER BY e.date_evenement ASC";
$stmtUpcoming = $pdo->prepare($sqlUpcoming);
$stmtUpcoming->execute([$userEmail]);
$upcomingEvents = $stmtUpcoming->fetchAll();

// 2. Récupérer les événements passés
$sqlPast = "SELECT e.*, c.nom as categorie_nom, i.date_inscription
            FROM inscriptions i
            INNER JOIN evenements e ON i.evenement_id = e.id
            LEFT JOIN categories c ON e.categorie_id = c.id
            WHERE i.email_participant = ? AND e.date_evenement < NOW()
            ORDER BY e.date_evenement DESC
            LIMIT 5";
$stmtPast = $pdo->prepare($sqlPast);
$stmtPast->execute([$userEmail]);
$pastEvents = $stmtPast->fetchAll();

// 3. Statistiques personnelles
$totalInscriptions = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE email_participant = ?");
$totalInscriptions->execute([$userEmail]);
$totalCount = $totalInscriptions->fetchColumn();

$thisMonthInscriptions = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE email_participant = ? AND MONTH(date_inscription) = MONTH(CURRENT_DATE()) AND YEAR(date_inscription) = YEAR(CURRENT_DATE())");
$thisMonthInscriptions->execute([$userEmail]);
$thisMonthCount = $thisMonthInscriptions->fetchColumn();

// 4. Récupérer les notifications récentes
$sqlNotifs = "SELECT n.*, e.titre as event_titre 
              FROM notifications n 
              LEFT JOIN evenements e ON n.evenement_id = e.id 
              WHERE n.user_id = ? 
              ORDER BY n.created_at DESC 
              LIMIT 5";
$stmtNotifs = $pdo->prepare($sqlNotifs);
$stmtNotifs->execute([$userId]);
$recentNotifs = $stmtNotifs->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3 mb-3">
            <?php if (!empty($_SESSION['avatar'])): ?>
                <img src="/gestion_evenements/<?= htmlspecialchars($_SESSION['avatar']) ?>" 
                     class="rounded-circle border border-3 border-white shadow" 
                     style="width: 80px; height: 80px; object-fit: cover;">
            <?php else: ?>
                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="display-5 fw-bold mb-0">Bonjour, <?= htmlspecialchars($_SESSION['nom']) ?> 👋</h1>
                <p class="mb-0 opacity-75">Bienvenue dans votre espace personnel</p>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5" style="margin-top: -50px;">
    
    <!-- STATISTIQUES CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-2">Événements à venir</p>
                            <h2 class="fw-bold mb-0"><?= count($upcomingEvents) ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-calendar-event text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-2">Total inscriptions</p>
                            <h2 class="fw-bold mb-0"><?= $totalCount ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-2">Ce mois-ci</p>
                            <h2 class="fw-bold mb-0"><?= $thisMonthCount ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-calendar-month text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small text-uppercase fw-bold mb-2">Événements passés</p>
                            <h2 class="fw-bold mb-0"><?= count($pastEvents) ?></h2>
                        </div>
                        <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock-history text-secondary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- COLONNE GAUCHE : Événements à venir -->
        <div class="col-lg-8">
            
            <!-- LISTE DES ÉVÉNEMENTS À VENIR -->
            <div class="card card-custom border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>Mes événements à venir
                        </h5>
                        <span class="badge bg-primary"><?= count($upcomingEvents) ?></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($upcomingEvents) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($upcomingEvents as $event): ?>
                                <div class="list-group-item p-4 border-0 border-bottom">
                                    <div class="row align-items-center g-3">
                                        <div class="col-auto">
                                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center" style="min-width: 80px;">
                                                <div class="fw-bold text-primary display-6 mb-0">
                                                    <?= date('d', strtotime($event['date_evenement'])) ?>
                                                </div>
                                                <div class="small text-muted text-uppercase">
                                                    <?= date('M', strtotime($event['date_evenement'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="mb-2">
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($event['categorie_nom']) ?>
                                                </span>
                                            </div>
                                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($event['titre']) ?></h5>
                                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                                <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($event['date_evenement'])) ?></span>
                                                <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($event['lieu']) ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="event_details.php?id=<?= $event['id'] ?>" 
                                               class="btn btn-outline-primary rounded-pill px-4">
                                                Détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center">
                            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                            <h5 class="text-muted mb-3">Aucun événement à venir</h5>
                            <p class="text-muted mb-4">Vous n'êtes inscrit à aucun événement pour le moment.</p>
                            <a href="events.php" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-search me-2"></i>Parcourir les événements
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ÉVÉNEMENTS PASSÉS -->
            <?php if (count($pastEvents) > 0): ?>
            <div class="card card-custom border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>Événements passés
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach($pastEvents as $event): ?>
                            <div class="list-group-item p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($event['titre']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($event['date_evenement'])) ?>
                                            <i class="bi bi-geo-alt ms-3 me-1"></i><?= htmlspecialchars($event['lieu']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-secondary">Terminé</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- COLONNE DROITE : Notifications et actions rapides -->
        <div class="col-lg-4">
            
            <!-- ACTIONS RAPIDES -->
            <div class="card card-custom border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Actions rapides
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="events.php" class="btn btn-outline-primary rounded-pill">
                            <i class="bi bi-search me-2"></i>Parcourir les événements
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi bi-person me-2"></i>Mon profil
                        </a>
                        <a href="notifications.php" class="btn btn-outline-info rounded-pill">
                            <i class="bi bi-bell me-2"></i>Mes notifications
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- NOTIFICATIONS RÉCENTES -->
            <div class="card card-custom border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-bell me-2 text-info"></i>Notifications
                        </h5>
                        <a href="notifications.php" class="btn btn-sm btn-link text-decoration-none">Tout voir</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentNotifs) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recentNotifs as $notif): ?>
                                <div class="list-group-item p-3 border-0 border-bottom <?= $notif['is_read'] == 0 ? 'bg-light' : '' ?>">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                                <i class="bi bi-info-circle text-info"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small"><?= htmlspecialchars($notif['message']) ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <i class="bi bi-bell-slash display-4 text-muted mb-2"></i>
                            <p class="text-muted mb-0 small">Aucune notification</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>
