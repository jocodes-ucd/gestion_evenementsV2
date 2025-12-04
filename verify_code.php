<?php
// verify_code.php
require 'includes/db.php';

$email = $_GET['email'] ?? '';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    if (!empty($email) && !empty($code)) {
        // 1. Vérifier si le code correspond à l'email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verification_token = ?");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();

        if ($user) {
            // 2. Activer le compte
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update->execute([$user['id']]);
            
            // Redirection vers login avec succès
            header("Location: login.php?msg=verified");
            exit;
        } else {
            $error = "Code incorrect ou expiré.";
        }
    } else {
        $error = "Veuillez entrer le code.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: 80vh;">
    <div class="card card-custom p-4 p-md-5 text-center" style="max-width: 450px; width: 100%;">
        
        <div class="mb-4 text-primary">
            <i class="bi bi-shield-lock-fill display-1"></i>
        </div>
        
        <h3 class="fw-bold mb-2">Vérification</h3>
        <p class="text-muted mb-4">Un code à 6 chiffres a été envoyé à <strong><?= htmlspecialchars($email) ?></strong>.</p>

        <?php if($error): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-4">
                <i class="bi bi-x-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="mb-4">
                <input type="text" name="code" class="form-control form-control-lg text-center fw-bold border-2" 
                       placeholder="123456" maxlength="6" style="letter-spacing: 5px; font-size: 1.5rem;" required autofocus>
            </div>

            <button type="submit" class="btn btn-gradient w-100 py-3 rounded-pill fw-bold shadow-sm">
                Vérifier le code
            </button>
        </form>
        
        <div class="mt-4 pt-3 border-top">
            <small class="text-muted">Vous n'avez rien reçu ?</small><br>
            <a href="register.php" class="text-decoration-none fw-bold small">Recommencer l'inscription</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>