<?php
// /admin/participants.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// Vérifier l'ID
if (!isset($_GET['id'])) {
    die("ID de l'événement manquant.");
}
$event_id = $_GET['id'];

// 1. Récupérer les infos de l'événement (pour le titre)
$stmt = $pdo->prepare("SELECT titre, nb_max_participants FROM evenements WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if(!$event) die("Événement introuvable.");

// 2. Récupérer la liste des inscrits
$stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE evenement_id = ? ORDER BY date_inscription DESC");
$stmt->execute([$event_id]);
$inscrits = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="bi bi-people"></i> Participants : 
            <span class="text-primary"><?= htmlspecialchars($event['titre']) ?></span>
        </h3>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center">
                    <h1 class="display-4 fw-bold text-success"><?= count($inscrits) ?></h1>
                    <p class="text-muted">Personnes inscrites</p>
                    <hr>
                    <small>Capacité max : <?= $event['nb_max_participants'] ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($inscrits) > 0): ?>
                                <?php foreach($inscrits as $p): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($p['nom_participant']) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($p['email_participant']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($p['email_participant']) ?>
                                            </a>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($p['date_inscription'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Aucun inscrit pour le moment.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="text-end mt-3">
                <button onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Imprimer la liste</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>