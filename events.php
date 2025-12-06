<?php
// events.php
require 'includes/db.php';
include 'includes/header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie_filter = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

// Build SQL query with filters
$sql = "SELECT e.*, c.nom as categorie_nom FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $sql .= " AND (e.titre LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Add category filter
if ($categorie_filter > 0) {
    $sql .= " AND e.categorie_id = :categorie";
    $params[':categorie'] = $categorie_filter;
}

$sql .= " ORDER BY e.date_evenement ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_events = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">
            <i class="bi bi-calendar3-range me-3"></i>Agenda Complet
        </h1>
        <p class="lead opacity-75">Retrouvez toutes les dates de nos formations, ateliers et événements.</p>
    </div>
</div>

<div class="container pb-5">
    <!-- Search and Filter Card -->
    <div class="card card-custom p-4 mb-4 shadow-lg" style="margin-top: -50px; position: relative; z-index: 10;">
        <form class="row g-3 align-items-center" method="GET">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="bi bi-search text-primary"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0 bg-light py-2" 
                           placeholder="Rechercher par titre, lieu ou description..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="categorie" class="form-select border-0 bg-light py-2">
                    <option value="">📂 Toutes catégories</option>
                    <?php
                    $catStmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
                    while($cat = $catStmt->fetch()):
                    ?>
                        <option value="<?= $cat['id'] ?>" <?= $categorie_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-gradient w-100 py-2 fw-bold">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrer
                </button>
            </div>
            <div class="col-md-2">
                <a href="events.php" class="btn btn-outline-secondary w-100 py-2 fw-bold">
                    <i class="bi bi-x-circle me-1"></i> Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Results Info Card -->
    <div class="card card-custom p-3 mb-4 border-0 shadow-sm">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="fw-bold text-dark">
                    <i class="bi bi-collection-fill me-2 text-primary"></i> 
                    <?php if (!empty($search) || $categorie_filter > 0): ?>
                        Résultats: <span class="badge bg-primary"><?= count($all_events) ?></span> événement(s) trouvé(s)
                    <?php else: ?>
                        Affichage de <span class="badge bg-primary"><?= count($all_events) ?></span> événement(s)
                    <?php endif; ?>
                </div>
                <?php if (!empty($search)): ?>
                    <small class="text-muted">Recherche: "<?= htmlspecialchars($search) ?>"</small>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php" class="btn btn-sm btn-outline-dark rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Retour Accueil
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if(count($all_events) > 0): ?>
            <?php foreach($all_events as $event): ?>
                <?php
                // Déterminer le statut de l'événement
                $eventDate = strtotime($event['date_evenement']);
                $today = strtotime(date('Y-m-d'));
                $eventDay = strtotime(date('Y-m-d', $eventDate));
                
                if ($eventDay == $today) {
                    $statusClass = 'status-ongoing';
                    $statusIcon = 'bi-broadcast';
                    $statusText = 'Aujourd\'hui';
                } elseif ($eventDate > time()) {
                    $statusClass = 'status-upcoming';
                    $statusIcon = 'bi-clock-fill';
                    $statusText = 'À venir';
                } else {
                    $statusClass = 'status-finished';
                    $statusIcon = 'bi-check-circle-fill';
                    $statusText = 'Terminé';
                }
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-custom h-100 group-hover border-0 shadow-sm">
                        <div style="height:200px; overflow: hidden; position: relative;">
                            <?php 
                                // GESTION IMAGE
                                if (!empty($event['image'])) {
                                    $img = "/gestion_evenements/" . $event['image'];
                                } else {
                                    $img = "https://images.unsplash.com/photo-1475721027767-9662938a5a63?w=800"; 
                                    if(stripos($event['categorie_nom'], 'conf') !== false) $img = "https://images.unsplash.com/photo-1544531586-fde5298cdd40?w=800";
                                    if(stripos($event['categorie_nom'], 'atelier') !== false) $img = "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800";
                                }
                            ?>
                            <img src="<?= htmlspecialchars($img) ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.5s;" 
                                 onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            
                            <!-- Badge Statut -->
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-dark fw-bold shadow-sm px-3 py-2 rounded-pill event-status <?= $statusClass ?>">
                                    <i class="bi <?= $statusIcon ?> me-1 <?= $statusClass == 'status-upcoming' ? 'text-success' : ($statusClass == 'status-ongoing' ? 'text-warning' : 'text-secondary') ?>"></i><?= $statusText ?>
                                </span>
                            </div>
                            
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-white text-dark fw-bold shadow px-3 py-2 rounded-pill">
                                    <i class="bi bi-tag-fill me-1 text-primary"></i>
                                    <?= htmlspecialchars($event['categorie_nom']) ?>
                                </span>
                            </div>
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 text-white fw-bold shadow px-3 py-2">
                                    <?= date('d M', strtotime($event['date_evenement'])) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="text-muted small fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary fs-5"></i>
                                <span><?= date('d F Y', strtotime($event['date_evenement'])) ?></span>
                                <span class="ms-auto">
                                    <i class="bi bi-clock-fill text-warning"></i>
                                    <?= date('H:i', strtotime($event['date_evenement'])) ?>
                                </span>
                            </div>
                            
                            <h4 class="fw-bold mb-3 text-dark" style="min-height: 56px;">
                                <?= htmlspecialchars($event['titre']) ?>
                            </h4>
                            
                            <div class="text-muted small mb-3 d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                                <span><?= htmlspecialchars($event['lieu']) ?></span>
                            </div>
                            
                            <p class="text-secondary small flex-grow-1 mb-4" style="min-height: 60px;">
                                <?= substr(htmlspecialchars($event['description']), 0, 100) ?>...
                            </p>
                            
                            <a href="event_details.php?id=<?= $event['id'] ?>" 
                               class="btn btn-primary fw-bold rounded-pill mt-auto w-100 py-2 shadow-sm">
                                <i class="bi bi-eye me-2"></i>Voir & Réserver
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm d-inline-block px-5 py-5">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold mb-2">Aucun événement trouvé</h4>
                    <p class="text-muted mb-4">
                        <?php if (!empty($search) || $categorie_filter > 0): ?>
                            Essayez de modifier vos critères de recherche
                        <?php else: ?>
                            L'agenda est vide pour le moment
                        <?php endif; ?>
                    </p>
                    <a href="events.php" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i>Réinitialiser les filtres
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>