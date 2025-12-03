<?php
// event_details.php
require 'includes/db.php';
include 'includes/header.php';

// 1. Vérification ID
if (!isset($_GET['id'])) { die("<div class='alert alert-danger'>ID manquant.</div>"); }
$id = $_GET['id'];

// 2. Récupérer l'événement
$stmt = $pdo->prepare("SELECT e.*, c.nom as cat_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) { die("<div class='alert alert-danger'>Événement introuvable.</div>"); }

// 3. Traitement du formulaire d'inscription
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));

    if (!empty($nom) && !empty($email)) {
        // Vérif places
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
        $countStmt->execute([$id]);
        $actuel = $countStmt->fetchColumn();

        if ($actuel < $event['nb_max_participants']) {
            // Vérif doublon
            $check = $pdo->prepare("SELECT id FROM inscriptions WHERE evenement_id = ? AND email_participant = ?");
            $check->execute([$id, $email]);
            
            if ($check->rowCount() == 0) {
                $pdo->prepare("INSERT INTO inscriptions (evenement_id, nom_participant, email_participant) VALUES (?, ?, ?)")
                    ->execute([$id, $nom, $email]);
                
                // Simulation envoi mail
                $message = "<div class='alert alert-success border-0 shadow-sm'><i class='bi bi-check-circle-fill'></i> Inscription confirmée ! Un email a été envoyé.</div>";
            } else {
                $message = "<div class='alert alert-warning border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill'></i> Vous êtes déjà inscrit.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger border-0 shadow-sm'>Complet ! Désolé.</div>";
        }
    }
}

// Calcul des places restantes pour la barre de progression
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
$countStmt->execute([$id]);
$inscrits = $countStmt->fetchColumn();
$restant = $event['nb_max_participants'] - $inscrits;
$percent = ($inscrits / $event['nb_max_participants']) * 100;
?>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-primary badge-custom me-2"><?= htmlspecialchars($event['cat_nom']) ?></span>
                <small class="text-muted"><i class="bi bi-clock"></i> Publié le <?= date('d/m/Y', strtotime($event['created_at'])) ?></small>
            </div>
            
            <h1 class="display-5 fw-bold text-dark mb-4"><?= htmlspecialchars($event['titre']) ?></h1>
            
            <div class="d-flex mb-4 text-secondary">
                <div class="me-4">
                    <i class="bi bi-calendar-check fs-4 text-primary"></i>
                    <div class="fw-bold">Date</div>
                    <div><?= date('d/m/Y à H:i', strtotime($event['date_evenement'])) ?></div>
                </div>
                <div>
                    <i class="bi bi-geo-alt fs-4 text-danger"></i>
                    <div class="fw-bold">Lieu</div>
                    <div><?= htmlspecialchars($event['lieu']) ?></div>
                </div>
            </div>

            <hr class="my-4" style="opacity: 0.1;">
            
            <h5 class="fw-bold"><i class="bi bi-file-text me-2"></i>À propos de cet événement</h5>
            <p class="lead text-muted mt-3" style="line-height: 1.8;">
                <?= nl2br(htmlspecialchars($event['description'])) ?>
            </p>
        </div>
        
        <a href="index.php" class="btn btn-light text-muted"><i class="bi bi-arrow-left"></i> Retour à la liste</a>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom shadow-lg border-0 sticky-top" style="top: 20px; z-index: 100;">
            <div class="card-header-custom text-center">
                <h4 class="mb-0 text-white"><i class="bi bi-ticket-perforated"></i> Réservation</h4>
            </div>
            <div class="card-body p-4">
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold text-muted">Places occupées</span>
                        <span class="fw-bold text-primary"><?= $inscrits ?> / <?= $event['nb_max_participants'] ?></span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-gradient" role="progressbar" style="width: <?= $percent ?>%; background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%);"></div>
                    </div>
                    <?php if($restant <= 5 && $restant > 0): ?>
                        <small class="text-danger fw-bold mt-1 d-block"><i class="bi bi-fire"></i> Vite ! Plus que <?= $restant ?> places !</small>
                    <?php endif; ?>
                </div>

                <?= $message ?>

                <?php if($restant > 0): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Votre Nom</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="nom" class="form-control bg-light border-0 py-2" placeholder="Jean Dupont" 
                                   value="<?= isset($_SESSION['nom']) ? $_SESSION['nom'] : '' ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold">Votre Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="jean@mail.com" 
                                   value="<?= isset($_SESSION['user_id']) ? 'user@test.com' : '' ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gradient w-100 py-2 fs-5 shadow-sm">
                        Je m'inscris <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>
                </form>
                <?php else: ?>
                    <button class="btn btn-secondary w-100 py-2" disabled>Complet</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>