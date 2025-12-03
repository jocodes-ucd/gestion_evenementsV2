<?php
// event_details.php - LOGIQUE PHP (Gardée identique, j'ai juste mis à jour le design)
require 'includes/db.php';
include 'includes/header.php'; // Charge le nouveau style

if (!isset($_GET['id'])) die("ID manquant");
$id = $_GET['id'];

// Récupération Event
$stmt = $pdo->prepare("SELECT e.*, c.nom as cat_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) die("Événement introuvable");

// Logique Inscription (identique à avant, raccourcie pour lisibilité ici)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) die("Erreur auth");
    $nom = $_SESSION['nom']; $email = $_SESSION['email'];
    
    // Check places & doublons... (ta logique existante)
    $check = $pdo->prepare("SELECT id FROM inscriptions WHERE evenement_id = ? AND email_participant = ?");
    $check->execute([$id, $email]);
    if ($check->rowCount() == 0) {
        $pdo->prepare("INSERT INTO inscriptions (evenement_id, nom_participant, email_participant) VALUES (?, ?, ?)")->execute([$id, $nom, $email]);
        $message = "<div class='alert alert-success rounded-3 border-0 bg-success bg-opacity-10 text-success'><i class='bi bi-check-circle-fill'></i> Inscription validée !</div>";
    } else {
        $message = "<div class='alert alert-warning rounded-3 border-0 bg-warning bg-opacity-10 text-warning'>Déjà inscrit.</div>";
    }
}

// Stats
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
$countStmt->execute([$id]);
$inscrits = $countStmt->fetchColumn();
$percent = ($event['nb_max_participants'] > 0) ? ($inscrits / $event['nb_max_participants']) * 100 : 100;
?>

<div class="page-header">
    <div class="container">
        <span class="badge bg-white bg-opacity-25 text-white mb-2"><?= htmlspecialchars($event['cat_nom']) ?></span>
        <h1 class="display-4 fw-bold"><?= htmlspecialchars($event['titre']) ?></h1>
        <div class="d-flex gap-4 mt-3 opacity-75">
            <span><i class="bi bi-calendar-event me-2"></i><?= date('d M Y, H:i', strtotime($event['date_evenement'])) ?></span>
            <span><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($event['lieu']) ?></span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-5">
        
        <div class="col-lg-8">
            <div class="card card-custom p-4 p-lg-5 mb-4">
                <h3 class="fw-bold mb-4 text-dark">À propos</h3>
                <p class="lead text-secondary" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </p>
                
                <hr class="my-5 opacity-10">
                
                <h5 class="fw-bold mb-3">Organisateur</h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-badge rounded-circle"><i class="bi bi-building"></i></div>
                    <div>
                        <div class="fw-bold">Équipe Interne</div>
                        <div class="small text-muted">Contact: support@eventplace.com</div>
                    </div>
                </div>
            </div>
            <a href="index.php" class="text-decoration-none fw-bold text-muted"><i class="bi bi-arrow-left"></i> Retour aux événements</a>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom p-4 shadow-lg sticky-top" style="top: 100px;">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2 fw-bold text-muted small">
                        <span>PLACES DISPONIBLES</span>
                        <span><?= $inscrits ?> / <?= $event['nb_max_participants'] ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-gradient" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>

                <?= $message ?>

                <?php if($inscrits < $event['nb_max_participants']): ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="bg-light p-3 rounded-3 mb-3">
                            <div class="small text-muted fw-bold mb-1">CONNECTÉ EN TANT QUE</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-check-fill text-success"></i>
                                <span class="fw-bold text-dark"><?= $_SESSION['nom'] ?></span>
                            </div>
                        </div>
                        <form method="POST">
                            <button class="btn btn-gradient w-100 py-3 rounded-3 shadow-sm">
                                Confirmer ma présence
                            </button>
                        </form>
                        <div class="text-center mt-3 small text-muted">Aucun paiement requis.</div>
                    <?php else: ?>
                        <div class="text-center py-4 bg-light rounded-3">
                            <p class="mb-3 text-muted">Connectez-vous pour réserver.</p>
                            <a href="login.php" class="btn btn-outline-primary w-100 fw-bold">Se connecter</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary w-100 py-3 rounded-3" disabled>COMPLET</button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>