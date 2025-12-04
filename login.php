<?php
// login.php
require 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            
            // --- VÉRIFICATION EMAIL ---
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                // Si le compte n'est pas vérifié, on propose de saisir le code
                $error = "Compte non activé. <a href='verify_code.php?email=".urlencode($email)."'>Entrer le code de validation</a>.";
            } else {
                // SUCCÈS
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['photo_profil'] = $user['photo_profil'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') header("Location: admin/index.php");
                else header("Location: index.php");
                exit;
            }
            // --------------------------

        } else {
            $error = "Identifiants incorrects.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: 80vh;">
    <div class="card card-custom p-4 p-md-5" style="max-width: 450px; width: 100%;">
        
        <div class="text-center mb-4">
            <div class="brand-badge mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                <i class="bi bi-person-circle"></i>
            </div>
            <h2 class="fw-bold">Bon retour !</h2>
            <p class="text-muted">Connectez-vous pour accéder à vos événements.</p>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'verify_needed'): ?>
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark rounded-3 text-center mb-4">
                <i class="bi bi-envelope-check-fill me-2"></i> Compte créé !<br>
                Nous avons envoyé un code de vérification à votre adresse email.
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'verified'): ?>
            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 text-center mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> Compte vérifié avec succès !<br>
                Vous pouvez maintenant vous connecter.
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 text-center">
                <i class="bi bi-exclamation-circle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control rounded-3 border-light bg-light" id="floatEmail" placeholder="name@example.com" required>
                <label for="floatEmail">Adresse Email</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control rounded-3 border-light bg-light" id="floatPass" placeholder="Password" required>
                <label for="floatPass">Mot de passe</label>
            </div>
            
            <button type="submit" class="btn btn-gradient w-100 py-3 rounded-3 fs-5">Se connecter</button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted">Pas encore de compte ?</span>
            <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--primaryA);">Créer un compte</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>