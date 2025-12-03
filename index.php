<?php
// index.php (Racine)
require 'includes/db.php';
include 'includes/header.php';

// Récupérer les événements futurs
$sql = "SELECT e.*, c.nom as categorie_nom 
        FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        ORDER BY e.date_evenement ASC";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<div class="jumbotron p-5 rounded bg-light mb-4 text-center">
    <h1 class="display-4">Bienvenue sur EventManager 📅</h1>
    <p class="lead">Découvrez nos prochaines conférences et réservez votre place.</p>
</div>

<div class="row">
    <?php if(count($events) > 0): ?>
        <?php foreach($events as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm event-card">
                    <div class="card-header bg-info text-white">
                        <?= htmlspecialchars($event['categorie_nom']) ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($event['titre']) ?></h5>
                        <p class="card-text text-muted">
                            <?= substr(htmlspecialchars($event['description']), 0, 100) ?>...
                        </p>
                        <p>
                            <strong>📅 Date :</strong> <?= date('d/m/Y à H:i', strtotime($event['date_evenement'])) ?> <br>
                            <strong>📍 Lieu :</strong> <?= htmlspecialchars($event['lieu']) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary w-100">Voir détails & S'inscrire</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning w-100">Aucun événement prévu pour le moment.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>