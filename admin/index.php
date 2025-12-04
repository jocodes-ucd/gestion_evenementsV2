<?php
// /admin/index.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// --- 1. DONNÉES TABLEAU ---
$sql = "SELECT e.*, c.nom as cat_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id ORDER BY date_evenement DESC";
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
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Liste des événements</h5>
            <span class="badge bg-light text-dark border"><?= count($events) ?> événements</span>
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
                                            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
                                            $countStmt->execute([$event['id']]);
                                            $nb_inscrits = $countStmt->fetchColumn();
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
                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percent ?>%"></div>
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
                            <tr><td colspan="5" class="text-center py-5 text-muted">Aucun événement à gérer.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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