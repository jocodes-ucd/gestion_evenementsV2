<?php
// profile.php
require 'includes/db.php';
include 'includes/header.php';

// Sécurité
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$message = "";
$userId = $_SESSION['user_id'];

// Récupérer les infos actuelles (pour pré-remplir le formulaire)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    
    // 1. GESTION DE L'UPLOAD PHOTO
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = $_FILES['avatar']['type'];
        $filesize = $_FILES['avatar']['size'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Vérifications (Extension + Taille max 2Mo)
        if (!in_array($extension, $allowed)) die("Erreur : Format d'image incorrect (JPG, PNG, GIF acceptés).");
        if ($filesize > 2 * 1024 * 1024) die("Erreur : L'image est trop lourde (Max 2Mo).");

        // Nouveau nom unique pour éviter les conflits
        $newFilename = "user_" . $userId . "_" . time() . "." . $extension;
        $destination = "uploads/avatars/" . $newFilename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            // Mise à jour BDD pour la photo
            $stmtPic = $pdo->prepare("UPDATE users SET photo_profil = ? WHERE id = ?");
            $stmtPic->execute([$destination, $userId]);
            
            // Mise à jour Session immédiate
            $_SESSION['photo_profil'] = $destination;
        }
    }

    // 2. MISE À JOUR TEXTE
    if (!empty($nom) && !empty($email)) {
        $stmtUpdate = $pdo->prepare("UPDATE users SET nom = ?, email = ? WHERE id = ?");
        $stmtUpdate->execute([$nom, $email, $userId]);
        
        // Mise à jour Session
        $_SESSION['nom'] = $nom;
        $_SESSION['email'] = $email;
        
        $message = "<div class='alert alert-success'>Profil mis à jour avec succès !</div>";
        
        // Rafraîchir les données locales
        $user['nom'] = $nom;
        $user['email'] = $email;
        if(isset($destination)) $user['photo_profil'] = $destination;
    }
}
?>

<div class="page-header">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-3">Mon Profil</h1>
        <p class="lead opacity-75">Gérez vos informations personnelles et votre avatar.</p>
    </div>
</div>

<div class="container pb-5" style="margin-top: -50px; position: relative; z-index: 10;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-5">
                
                <?= $message ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row align-items-center mb-5">
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                <?php 
                                    $avatar = !empty($user['photo_profil']) ? $user['photo_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($user['nom']).'&background=random'; 
                                ?>
                                <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle shadow-lg" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid white;">
                                <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm" style="cursor: pointer;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-1">Photo de profil</h4>
                            <p class="text-muted small">Cliquez sur l'icône caméra pour changer votre photo.<br>Formats: JPG, PNG. Max 2Mo.</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NOM COMPLET</label>
                        <input type="text" name="nom" class="form-control bg-light border-0 py-3" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">ADRESSE EMAIL</label>
                        <input type="email" name="email" class="form-control bg-light border-0 py-3" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="index.php" class="btn btn-light fw-bold px-4 py-3 rounded-pill">Annuler</a>
                        <button type="submit" class="btn btn-gradient px-5 py-3 rounded-pill shadow-sm">Enregistrer les modifications</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>