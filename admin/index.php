<?php
// /admin/index.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// --- ONGLET ACTIF ---
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
$validTabs = ['upcoming', 'ongoing', 'finished', 'all'];
if (!in_array($activeTab, $validTabs)) $activeTab = 'all';

// --- STATISTICS QUERIES ---
$totalEvents = $pdo->query("SELECT COUNT(*) FROM evenements")->fetchColumn();
$totalRegistrations = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
$thisMonthEvents = $pdo->query("SELECT COUNT(*) FROM evenements WHERE MONTH(date_evenement) = MONTH(CURRENT_DATE()) AND YEAR(date_evenement) = YEAR(CURRENT_DATE())")->fetchColumn();
$upcomingEventsCount = $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement > NOW()")->fetchColumn();
$ongoingEventsCount = $pdo->query("SELECT COUNT(*) FROM evenements WHERE DATE(date_evenement) = CURDATE()")->fetchColumn();
$finishedEventsCount = $pdo->query("SELECT COUNT(*) FROM evenements WHERE date_evenement < NOW() AND DATE(date_evenement) != CURDATE()")->fetchColumn();

// --- FILTRAGE SELON L'ONGLET ---
$whereClause = "";
switch ($activeTab) {
    case 'upcoming':
        $whereClause = "WHERE e.date_evenement > NOW()";
        break;
    case 'ongoing':
        $whereClause = "WHERE DATE(e.date_evenement) = CURDATE()";
        break;
    case 'finished':
        $whereClause = "WHERE e.date_evenement < NOW() AND DATE(e.date_evenement) != CURDATE()";
        break;
    case 'all':
    default:
        $whereClause = "";
        break;
}

// --- PAGINATION VARIABLES ---
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Count for current filter
$countSql = "SELECT COUNT(*) FROM evenements e $whereClause";
$totalCount = $pdo->query($countSql)->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

// Ensure page is within valid range
if ($page > $totalPages) $page = $totalPages;

// --- 1. DONNÉES TABLEAU (with pagination and filter) ---
$sql = "SELECT e.*, c.nom as cat_nom,
        (SELECT COUNT(*) FROM inscriptions WHERE evenement_id = e.id) as nb_inscrits
        FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        $whereClause 
        ORDER BY date_evenement DESC 
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();

// --- 2. DONNÉES GRAPHIQUES ---
// (Barres)
$sqlBar = "SELECT e.titre, COUNT(i.id) as total FROM evenements e LEFT JOIN inscriptions i ON e.id = i.evenement_id GROUP BY e.id ORDER BY total DESC LIMIT 5";
$stmtBar = $pdo->query($sqlBar);
$dataBar = $stmtBar->fetchAll();
$labelsBar = []; $countsBar = [];
foreach($dataBar as $d) { $labelsBar[] = substr($d['titre'], 0, 15).'...'; $countsBar[] = $d['total']; }

// (Courbe)
$sqlLine = "SELECT DATE(date_inscription) as jour, COUNT(*) as total FROM inscriptions WHERE date_inscription >= DATE(NOW()) - INTERVAL 7 DAY GROUP BY DATE(date_inscription) ORDER BY jour ASC";
$stmtLine = $pdo->query($sqlLine);
$dataLine = $stmtLine->fetchAll();
$labelsLine = []; $countsLine = [];
for ($i=6; $i>=0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days")); $labelsLine[]=date('d/m', strtotime($date));
    $found=false; foreach($dataLine as $d){ if($d['jour']==$date){ $countsLine[]=$d['total']; $found=true; break; } }
    if(!$found) $countsLine[]=0;
}
?>

<div class="container py-5">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h2 class="fw-bold display-6 mb-0 text-dark">Tableau de Bord</h2>
            <p class="text-muted mb-0">Vue d'ensemble et gestion rapide.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-dark rounded-pill fw-bold px-4">
                <i class="bi bi-eye me-2"></i>Site Public
            </a>
            <a href="create_event.php" class="btn btn-gradient rounded-pill fw-bold px-4 shadow-sm text-white">
                <i class="bi bi-plus-lg me-2"></i>Créer
            </a>
        </div>
    </div>

    <!-- STATISTICS CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small text-uppercase fw-bold mb-2">Total Événements</p>
                        <h3 class="fw-bold mb-0"><?= $totalEvents ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-calendar-event text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small text-uppercase fw-bold mb-2">À Venir</p>
                        <h3 class="fw-bold mb-0"><?= $upcomingEventsCount ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-arrow-up-circle text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small text-uppercase fw-bold mb-2">Inscriptions</p>
                        <h3 class="fw-bold mb-0"><?= $totalRegistrations ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-people text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small text-uppercase fw-bold mb-2">Terminés</p>
                        <h3 class="fw-bold mb-0"><?= $finishedEventsCount ?></h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-check-circle text-secondary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card card-custom h-100 p-4 border-0 shadow-sm">
                <h6 class="text-uppercase text-muted fw-bold small ls-1 mb-4">Top Popularité</h6>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-custom h-100 p-4 border-0 shadow-sm">
                <h6 class="text-uppercase text-muted fw-bold small ls-1 mb-4">Inscriptions (7 jours)</h6>
                <div style="position: relative; height: 220px; width: 100%;">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Liste des événements</h5>
                <span class="badge bg-light text-dark border"><?= $totalCount ?> événement(s)</span>
            </div>
            
            <!-- ONGLETS DE FILTRAGE - STYLE AMÉLIORÉ -->
            <div class="d-flex flex-wrap gap-2" id="eventTabs">
                <a class="btn <?= $activeTab == 'all' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-4 py-2 fw-semibold" 
                   href="?tab=all">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Tous
                    <span class="badge <?= $activeTab == 'all' ? 'bg-white text-primary' : 'bg-secondary' ?> ms-2"><?= $totalEvents ?></span>
                </a>
                <a class="btn <?= $activeTab == 'upcoming' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-4 py-2 fw-semibold" 
                   href="?tab=upcoming">
                    <i class="bi bi-clock-fill me-2"></i>À venir
                    <span class="badge <?= $activeTab == 'upcoming' ? 'bg-white text-success' : 'bg-success' ?> ms-2"><?= $upcomingEventsCount ?></span>
                </a>
                <a class="btn <?= $activeTab == 'ongoing' ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill px-4 py-2 fw-semibold" 
                   href="?tab=ongoing">
                    <i class="bi bi-broadcast me-2"></i>En cours
                    <span class="badge <?= $activeTab == 'ongoing' ? 'bg-white text-warning' : 'bg-warning text-dark' ?> ms-2"><?= $ongoingEventsCount ?></span>
                </a>
                <a class="btn <?= $activeTab == 'finished' ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill px-4 py-2 fw-semibold" 
                   href="?tab=finished">
                    <i class="bi bi-check-circle-fill me-2"></i>Terminés
                    <span class="badge <?= $activeTab == 'finished' ? 'bg-white text-secondary' : 'bg-secondary' ?> ms-2"><?= $finishedEventsCount ?></span>
                </a>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4 text-muted small text-uppercase fw-bold">Event</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Date & Lieu</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Statut / Timer</th> <th class="py-3 text-muted small text-uppercase fw-bold">Places</th>
                            <th class="py-3 text-end pe-4 text-muted small text-uppercase fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($events) > 0): ?>
                            <?php foreach($events as $event): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 50px; height: 50px; flex-shrink:0;">
                                                <?php if(!empty($event['image'])): ?>
                                                    <img src="/gestion_evenements/<?= htmlspecialchars($event['image']) ?>" 
                                                         class="rounded-3 shadow-sm w-100 h-100 object-fit-cover">
                                                <?php else: ?>
                                                    <div class="w-100 h-100 bg-light rounded-3 d-flex align-items-center justify-content-center text-muted border">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 200px;">
                                                    <?= htmlspecialchars($event['titre']) ?>
                                                </div>
                                                <span class="badge bg-white border text-muted fw-normal rounded-pill" style="font-size: 0.7rem;">
                                                    <?= htmlspecialchars($event['cat_nom']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span class="fw-bold text-dark">
                                                <i class="bi bi-calendar-event me-1 text-primary"></i> 
                                                <?= date('d/m/Y', strtotime($event['date_evenement'])) ?>
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-clock me-1"></i> 
                                                <?= date('H:i', strtotime($event['date_evenement'])) ?>
                                            </span>
                                            <span class="text-muted text-truncate" style="max-width: 150px;">
                                                <i class="bi bi-geo-alt me-1"></i> 
                                                <?= htmlspecialchars($event['lieu']) ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="countdown-dynamic badge rounded-pill px-3 py-2 fw-normal" 
                                              data-date="<?= date('Y-m-d\TH:i:s', strtotime($event['date_evenement'])) ?>">
                                            Chargement...
                                        </span>
                                    </td>

                                    <td style="width: 15%;">
                                        <?php 
                                            $nb_inscrits = $event['nb_inscrits'];
                                            $percent = ($event['nb_max_participants'] > 0) ? ($nb_inscrits / $event['nb_max_participants']) * 100 : 0;
                                            
                                            $color = 'success';
                                            if ($percent >= 100) $color = 'danger'; // Complet
                                            elseif ($percent >= 75) $color = 'warning'; // Presque complet
                                        ?>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small fw-bold text-muted"><?= $nb_inscrits ?> / <?= $event['nb_max_participants'] ?></span>
                                            <span class="small fw-bold text-<?= $color ?>"><?= round($percent) ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= min($percent, 100) ?>%"></div>
                                        </div>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm border" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3">
                                                <li><h6 class="dropdown-header small text-uppercase">Gérer</h6></li>
                                                <li><a class="dropdown-item" href="participants.php?id=<?= $event['id'] ?>"><i class="bi bi-people me-2 text-info"></i> Participants</a></li>
                                                <li><a class="dropdown-item" href="edit_event.php?id=<?= $event['id'] ?>"><i class="bi bi-pencil me-2 text-warning"></i> Modifier</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="delete_event.php?id=<?= $event['id'] ?>" onclick="return confirm('Supprimer définitivement ?');"><i class="bi bi-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-calendar-x display-4 text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">
                                            <?php 
                                            $emptyMessages = [
                                                'all' => 'Aucun événement à gérer.',
                                                'upcoming' => 'Aucun événement à venir.',
                                                'ongoing' => 'Aucun événement en cours aujourd\'hui.',
                                                'finished' => 'Aucun événement terminé.'
                                            ];
                                            echo $emptyMessages[$activeTab];
                                            ?>
                                        </p>
                                        <?php if ($activeTab != 'all'): ?>
                                        <a href="?tab=all" class="btn btn-outline-primary btn-sm mt-3 rounded-pill">
                                            <i class="bi bi-grid me-1"></i>Voir tous les événements
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- PAGINATION CONTROLS -->
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top p-4">
            <nav aria-label="Event pagination">
                <ul class="pagination justify-content-center mb-3">
                    <!-- Previous Button -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill me-2" href="?tab=<?= $activeTab ?>&page=<?= max(1, $page - 1) ?>" aria-label="Précédent">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    // Smart pagination: show first, last, current and nearby pages
                    $range = 2; // Number of pages to show on each side of current page
                    
                    for ($i = 1; $i <= $totalPages; $i++):
                        // Show first page, last page, current page, and pages within range
                        if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)):
                    ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link rounded-pill mx-1" href="?tab=<?= $activeTab ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php
                        // Show ellipsis
                        elseif ($i == $page - $range - 1 || $i == $page + $range + 1):
                    ?>
                        <li class="page-item disabled">
                            <span class="page-link border-0">...</span>
                        </li>
                    <?php
                        endif;
                    endfor;
                    ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill ms-2" href="?tab=<?= $activeTab ?>&page=<?= min($totalPages, $page + 1) ?>" aria-label="Suivant">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <p class="text-center text-muted small mb-0">
                    Page <?= $page ?> sur <?= $totalPages ?> • <?= $totalCount ?> événement<?= $totalCount > 1 ? 's' : '' ?> 
                    <?php 
                    $tabLabels = [
                        'all' => 'au total',
                        'upcoming' => 'à venir',
                        'ongoing' => 'en cours',
                        'finished' => 'terminé(s)'
                    ];
                    echo $tabLabels[$activeTab];
                    ?>
                </p>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. CHARTS (Inchangés)
    const commonOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, layout: { padding: 10 } };
    
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: <?= json_encode($labelsBar) ?>, datasets: [{ data: <?= json_encode($countsBar) ?>, backgroundColor: '#6d28d9', borderRadius: 6, barThickness: 25 }] },
        options: { ...commonOptions, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: <?= json_encode($labelsLine) ?>, datasets: [{ data: <?= json_encode($countsLine) ?>, borderColor: '#db2777', backgroundColor: 'rgba(219, 39, 119, 0.1)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4 }] },
        options: { ...commonOptions, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
    });

    // 2. COMPTE A REBOURS MULTIPLE (Tableau)
    function updateCountdowns() {
        const triggers = document.querySelectorAll('.countdown-dynamic');
        
        triggers.forEach(el => {
            const dateStr = el.getAttribute('data-date');
            const targetDate = new Date(dateStr).getTime();
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff < 0) {
                // Passé
                el.innerHTML = '<i class="bi bi-check-all"></i> Terminé';
                el.className = 'badge bg-secondary bg-opacity-10 text-secondary border-0';
            } else {
                // Futur
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                
                // Si c'est urgent (moins de 24h) -> ROUGE
                if (days === 0 && hours < 24) {
                    el.innerHTML = `<i class="bi bi-alarm-fill animate-pulse"></i> ${hours}h restants`;
                    el.className = 'badge bg-danger bg-opacity-10 text-danger border-0';
                } else {
                    // Normal -> VIOLET (Primary) au lieu de BLEU (Info)
                    el.innerHTML = `Dans ${days}j ${hours}h`;
                    // MODIFICATION ICI : bg-primary au lieu de bg-info
                    el.className = 'badge bg-primary bg-opacity-10 text-primary border-0';
                }
            }
        });
    }

    // Mise à jour toutes les secondes
    setInterval(updateCountdowns, 1000);
    updateCountdowns(); // Premier appel immédiat
</script>