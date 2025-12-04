<?php
// register.php
require 'includes/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);
    $pass_confirm = trim($_POST['password_confirm']);

    if (!empty($nom) && !empty($email) && !empty($pass)) {
        if ($pass === $pass_confirm) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() == 0) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $code = rand(100000, 999999); 

                $insert = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, role, is_verified, verification_token) VALUES (?, ?, ?, 'user', 0, ?)");
                
                if ($insert->execute([$nom, $email, $hash, $code])) {
                    
                    // --- ENVOI EMAIL RÉEL (PHPMailer) ---
                    require_once 'includes/mailer.php';

                    $subject = "Votre code de validation - EventPlace";
                    $body = "Bonjour $nom,\n\nBienvenue sur la plateforme !\n\nVoici votre code de validation à 6 chiffres :\n\n<h1>$code</h1>\n\nEntrez ce code sur la page de vérification pour activer votre compte.\n\nÀ bientôt,\nL'équipe EventPlace.";
                    
                    // Envoi via Gmail/Mailtrap
                    if (sendEmail($email, $subject, $body)) {
                        // Succès : On redirige vers la saisie du code
                        header("Location: verify_code.php?email=" . urlencode($email));
                        exit;
                    } else {
                        // Échec d'envoi (Mauvais mot de passe Gmail ?)
                        // On supprime l'utilisateur pour qu'il puisse réessayer
                        $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
                        $error = "Impossible d'envoyer l'email. Vérifiez votre connexion ou contactez l'admin.";
                    }
                    
                } else {
                    $error = "Erreur technique SQL.";
                }
            } else {
                $error = "Email déjà utilisé.";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Remplissez tous les champs.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: 80vh;">
    <div class="card card-custom p-4 p-md-5" style="max-width: 500px; width: 100%;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Créer un compte</h2>
            <p class="text-muted">Rejoignez EventPlace.</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger text-center border-0 bg-danger bg-opacity-10 text-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">NOM</label>
                <input type="text" name="nom" class="form-control bg-light border-0 py-2" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">EMAIL</label>
                <input type="email" name="email" class="form-control bg-light border-0 py-2" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold text-muted">MOT DE PASSE</label>
                    <input type="password" name="password" class="form-control bg-light border-0 py-2" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold text-muted">CONFIRMER</label>
                    <input type="password" name="password_confirm" class="form-control bg-light border-0 py-2" required>
                </div>
            </div>
            <button type="submit" class="btn btn-gradient w-100 py-3 rounded-pill fw-bold shadow-sm mt-2">S'inscrire</button>
        </form>
        <div class="text-center mt-4 border-top pt-3">
            <span class="text-muted">Déjà un compte ?</span> <a href="login.php" class="fw-bold text-decoration-none" style="color:var(--primaryA)">Se connecter</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>