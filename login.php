<?php
// --- LOGIQUE PHP (BACKEND) ---
require 'includes/db.php';
// session_start() est déjà dans header.php, mais on l'inclut après
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Le mot de passe tapé par l'utilisateur

    if (!empty($email) && !empty($password)) {
        // Requête préparée pour éviter les injections SQL
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérification du mot de passe haché
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // SUCÈS : On enregistre l'utilisateur dans la session
            session_start(); // On s'assure que la session est active pour stocker les données
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "❌ Email ou mot de passe incorrect.";
        }
    } else {
        $error = "❌ Veuillez remplir tous les champs.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-header bg-white text-center py-3">
                <h3 class="mb-0">🔐 Connexion</h3>
            </div>
            <div class="card-body p-4">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="admin@test.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required placeholder="password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">Se connecter</button>
                </form>
            </div>
            <div class="card-footer text-center bg-light">
                <small class="text-muted">Pas encore de compte ? Contactez l'admin.</small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>