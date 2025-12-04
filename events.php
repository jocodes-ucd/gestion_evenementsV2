<?php
// events.php
require 'includes/db.php';
include 'includes/header.php';

// Récupérer TOUS les événements
$sql = "SELECT e.*, c.nom as categorie_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id ORDER BY e.date_evenement ASC";
$stmt = $pdo->query($sql);
$all_events = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">Agenda Complet</h1>
        <p class="lead opacity-75">Retrouvez toutes les dates de nos formations et ateliers.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="card card-custom p-4 mb-5" style="margin-top: -50px; position: relative; z-index: 10;">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="fw-bold text-muted"><i class="bi bi-collection-fill me-2 text-primary"></i> Affichage de <?= count($all_events) ?> événement(s)</div>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left"></i> Retour Accueil</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if(count($all_events) > 0): ?>
            <?php foreach($all_events as $event): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-custom h-100 group-hover">
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
                            <div class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white shadow-sm">
                                <?= date('d M', strtotime($event['date_evenement'])) ?>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-uppercase fw-bold text-primary mb-2"><?= htmlspecialchars($event['categorie_nom']) ?></small>
                            <h4 class="fw-bold mb-2"><?= htmlspecialchars($event['titre']) ?></h4>
                            <div class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($event['lieu']) ?></div>
                            <p class="text-secondary small flex-grow-1"><?= substr(htmlspecialchars($event['description']), 0, 80) ?>...</p>
                            <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-outline-dark fw-bold rounded-3 mt-3 w-100">Voir & Réserver</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><h4 class="text-muted">Aucun événement dans l'agenda.</h4></div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>