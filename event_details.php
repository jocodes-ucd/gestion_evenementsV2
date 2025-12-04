<?php
// event_details.php
require 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['id'])) die("<div class='container mt-5 alert alert-danger'>ID manquant</div>");
$id = $_GET['id'];

// 1. Récupération Event
$stmt = $pdo->prepare("SELECT e.*, c.nom as cat_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) die("<div class='container mt-5 alert alert-danger'>Événement introuvable</div>");

// 2. Vérification statut inscription
$isRegistered = false;
if (isset($_SESSION['email'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM inscriptions WHERE evenement_id = ? AND email_participant = ?");
    $checkStmt->execute([$id, $_SESSION['email']]);
    if ($checkStmt->rowCount() > 0) $isRegistered = true;
}

// 3. Traitement POST (Inscription/Désinscription)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) die("Erreur auth");
    $nom = $_SESSION['nom']; $email = $_SESSION['email'];

    if (isset($_POST['cancel_registration'])) {
        $del = $pdo->prepare("DELETE FROM inscriptions WHERE evenement_id = ? AND email_participant = ?");
        $del->execute([$id, $email]);
        $pdo->prepare("INSERT INTO notifications (user_id, evenement_id, message) VALUES (?, ?, ?)")
            ->execute([$_SESSION['user_id'], $id, "Inscription annulée : " . $event['titre']]);
        $isRegistered = false;
        $message = "<div class='alert alert-info rounded-3 border-0 bg-info bg-opacity-10 text-info'><i class='bi bi-info-circle-fill'></i> Inscription annulée.</div>";
    }
    elseif (isset($_POST['confirm_registration']) && !$isRegistered) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
        $countStmt->execute([$id]);
        if ($countStmt->fetchColumn() < $event['nb_max_participants']) {
            $pdo->prepare("INSERT INTO inscriptions (evenement_id, nom_participant, email_participant) VALUES (?, ?, ?)")
                ->execute([$id, $nom, $email]);
            $pdo->prepare("INSERT INTO notifications (user_id, evenement_id, message) VALUES (?, ?, ?)")
                ->execute([$_SESSION['user_id'], $id, "Inscription confirmée : " . $event['titre']]);
            $isRegistered = true;
            $message = "<div class='alert alert-success rounded-3 border-0 bg-success bg-opacity-10 text-success'><i class='bi bi-check-circle-fill'></i> Inscription validée !</div>";
        } else {
            $message = "<div class='alert alert-danger'>Complet !</div>";
        }
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
                <?php if (!empty($event['image'])): ?>
                    <img src="/gestion_evenements/<?= htmlspecialchars($event['image']) ?>" class="img-fluid rounded-4 mb-4 shadow-sm w-100" style="height: 350px; object-fit: cover;">
                <?php endif; ?>

                <h3 class="fw-bold mb-4 text-dark">À propos</h3>
                <p class="lead text-secondary" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </p>
            </div>
            <a href="index.php" class="text-decoration-none fw-bold text-muted"><i class="bi bi-arrow-left"></i> Retour</a>
        </div>

        <div class="col-lg-4">
            
            <div class="card card-custom p-4 shadow-sm mb-4 border-primary border-2">
                <div class="text-center" id="countdown-box">
                    <h6 class="text-uppercase text-muted fw-bold small ls-1 mb-3">Temps Restant</h6>
                    <div class="d-flex justify-content-center gap-3" id="timer-display">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="timer-message" class="fw-bold text-danger mt-2 d-none"></div>
                </div>
            </div>

            <div class="card card-custom p-4 shadow-lg sticky-top" style="top: 20px;">
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

                <?php if($isRegistered): ?>
                    <div class="bg-light p-4 rounded-4 text-center border mb-3">
                        <div class="mb-3 text-success"><i class="bi bi-check-circle-fill display-4"></i></div>
                        <h5 class="fw-bold text-success mb-2">Inscrit !</h5>
                        <form method="POST" onsubmit="return confirm('Annuler ?');">
                            <button type="submit" name="cancel_registration" class="btn btn-outline-danger rounded-pill px-4 btn-sm fw-bold mt-2">
                                Se désinscrire
                            </button>
                        </form>
                    </div>
                <?php elseif($inscrits >= $event['nb_max_participants']): ?>
                    <button class="btn btn-secondary w-100 py-3 rounded-3" disabled>COMPLET</button>
                <?php else: ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="POST">
                            <button type="submit" name="confirm_registration" class="btn btn-gradient w-100 py-3 rounded-3 shadow-sm">
                                Confirmer ma présence
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4 bg-light rounded-3">
                            <a href="login.php" class="btn btn-outline-primary w-100 fw-bold">Se connecter</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // On passe la date PHP à JS
    const eventDate = new Date("<?= date('Y-m-d\TH:i:s', strtotime($event['date_evenement'])) ?>").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const distance = eventDate - now;

        const box = document.getElementById('timer-display');
        const msg = document.getElementById('timer-message');

        if (distance < 0) {
            // Événement passé
            box.innerHTML = '<h3 class="text-muted">Événement terminé</h3>';
            msg.classList.add('d-none');
            return;
        }

        // Calculs temps
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Affichage HTML joli
        box.innerHTML = `
            <div class="text-center">
                <div class="display-6 fw-bold text-dark">${days}</div>
                <div class="small text-muted text-uppercase">Jours</div>
            </div>
            <div class="display-6 fw-bold text-muted">:</div>
            <div class="text-center">
                <div class="display-6 fw-bold text-dark">${hours}</div>
                <div class="small text-muted text-uppercase">Heures</div>
            </div>
            <div class="display-6 fw-bold text-muted">:</div>
            <div class="text-center">
                <div class="display-6 fw-bold text-dark">${minutes}</div>
                <div class="small text-muted text-uppercase">Min</div>
            </div>
        `;
        
        // Alerte si moins de 24h (visuelle)
        if (days === 0 && hours < 24) {
            msg.innerHTML = "<i class='bi bi-alarm-fill'></i> C'est bientôt !";
            msg.classList.remove('d-none');
        }
    }

    // Lancer le timer
    setInterval(updateTimer, 1000);
    updateTimer();
</script>