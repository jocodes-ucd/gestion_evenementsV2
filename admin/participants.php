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
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if(!$event) die("Événement introuvable.");

// 2. Récupérer la liste des inscrits
$stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE evenement_id = ? ORDER BY nom_participant ASC");
$stmt->execute([$event_id]);
$inscrits = $stmt->fetchAll();
?>

<!-- STYLES D'IMPRESSION -->
<style>
/* À l'écran : cacher la zone d'impression */
@media screen {
    .print-area {
        display: none !important;
    }
}

/* À l'impression */
@media print {
    /* Cacher tout ce qui n'est pas la zone d'impression */
    body > *:not(.print-area) {
        display: none !important;
    }
    
    .navbar, nav, header, footer, .no-print {
        display: none !important;
    }
    
    /* Afficher et styler la zone d'impression */
    .print-area {
        display: block !important;
        width: 100%;
        margin: 0;
        padding: 20px;
    }
    
    .print-header {
        text-align: center;
        border-bottom: 3px solid #000;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    
    .print-header h1 {
        font-size: 22px;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #000;
    }
    
    .print-header .event-info {
        font-size: 14px;
        color: #333;
    }
    
    .print-summary {
        background: #f0f0f0;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #999;
        font-size: 13px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    
    .print-table th {
        background: #333 !important;
        color: #fff !important;
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .print-table td {
        border: 1px solid #000;
        padding: 6px 8px;
    }
    
    .print-table .row-number {
        width: 35px;
        text-align: center;
    }
    
    .print-footer {
        margin-top: 25px;
        padding-top: 15px;
        border-top: 1px solid #000;
        font-size: 10px;
        color: #666;
        overflow: hidden;
    }
    
    .print-footer .left { float: left; }
    .print-footer .right { float: right; }
}
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3>
            <i class="bi bi-people"></i> Participants : 
            <span class="text-primary"><?= htmlspecialchars($event['titre']) ?></span>
        </h3>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row no-print">
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
            
            <div class="d-flex gap-2 justify-content-end mt-3 no-print">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Imprimer la liste
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ZONE D'IMPRESSION OPTIMISÉE (en dehors du container) -->
<div class="print-area">
    <div class="print-header">
        <h1>LISTE DES PARTICIPANTS</h1>
        <div class="event-info">
            <strong style="font-size: 18px;"><?= htmlspecialchars($event['titre']) ?></strong><br>
            <span>Date: <?= date('d/m/Y à H:i', strtotime($event['date_evenement'])) ?></span>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <span>Lieu: <?= htmlspecialchars($event['lieu']) ?></span>
        </div>
    </div>
    
    <div class="print-summary">
        <strong>Récapitulatif :</strong> 
        <?= count($inscrits) ?> participant(s) inscrit(s) sur <?= $event['nb_max_participants'] ?> places disponibles
        (<?= $event['nb_max_participants'] > 0 ? round((count($inscrits) / $event['nb_max_participants']) * 100) : 0 ?>% de remplissage)
    </div>

    <?php if(count($inscrits) > 0): ?>
        <table class="print-table">
            <thead>
                <tr>
                    <th class="row-number">N°</th>
                    <th>Nom du participant</th>
                    <th>Adresse email</th>
                    <th>Date d'inscription</th>
                    <th style="width: 80px; text-align: center;">Présent</th>
                </tr>
            </thead>
            <tbody>
                <?php $num = 1; foreach($inscrits as $p): ?>
                    <tr>
                        <td class="row-number"><?= $num++ ?></td>
                        <td><strong><?= htmlspecialchars($p['nom_participant']) ?></strong></td>
                        <td><?= htmlspecialchars($p['email_participant']) ?></td>
                        <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
                        <td style="text-align: center;">☐</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">Aucun participant inscrit pour cet événement.</p>
    <?php endif; ?>
    
    <div class="print-footer">
        <span class="left">Liste générée le <?= date('d/m/Y à H:i') ?></span>
        <span class="right">Gestion des Événements - Administration</span>
    </div>
</div>

<?php include '../includes/footer.php'; ?>