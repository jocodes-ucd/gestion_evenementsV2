<?php
// admin/edit_event.php
require '../includes/db.php';
require 'auth_check.php';

if (!isset($_GET['id'])) { die("ID manquant."); }
$id = $_GET['id'];
$message = "";

// 1. TRAITEMENT FORMULAIRE (Mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date'];
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];
    
    // Gestion Image
    $oldImage = $_POST['current_image'];
    $imagePath = $oldImage; // Par défaut, on garde l'ancienne

    // Si une NOUVELLE image est envoyée
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
            $newName = "event_" . time() . "." . $ext;
            $target = "uploads/events/" . $newName;
            // Upload depuis le dossier admin vers le dossier uploads (../)
            if (move_uploaded_file($_FILES['image']['tmp_name'], "../" . $target)) {
                $imagePath = $target; 
            }
        }
    }

    if (!empty($titre)) {
        $sql = "UPDATE evenements 
                SET titre=?, description=?, date_evenement=?, lieu=?, categorie_id=?, nb_max_participants=?, image=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max, $imagePath, $id])) {
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur SQL lors de la mise à jour.</div>";
        }
    }
}

// 2. RECUPÉRATION DONNÉES ACTUELLES
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { die("Événement introuvable."); }

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                
                <div class="card-header bg-warning p-4">
                    <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square me-2"></i>Modifier l'événement</h4>
                    <p class="mb-0 text-dark opacity-75 small">Mettez à jour les informations ci-dessous</p>
                </div>

                <div class="card-body p-5 bg-white">
                    
                    <?= $message ?>

                    <form method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($event['image'] ?? '') ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1">Titre</label>
                            <input type="text" name="titre" class="form-control form-control-lg border-2 border-warning" 
                                   value="<?= htmlspecialchars($event['titre']) ?>" required>
                        </div>

                        <div class="mb-4 p-3 bg-light rounded-3 border border-2">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1 mb-3">Image de couverture</label>
                            <div class="d-flex align-items-center gap-4">
                                <div class="position-relative">
                                    <?php if(!empty($event['image'])): ?>
                                        <img src="/gestion_evenements/<?= $event['image'] ?>" class="rounded shadow-sm border" style="width: 100px; height: 100px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">Actuelle</span>
                                    <?php else: ?>
                                        <div class="rounded bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                            <small>Aucune</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control border-2" accept="image/*">
                                    <div class="form-text mt-2">Laissez vide pour conserver l'image actuelle.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Date & Heure</label>
                                <input type="datetime-local" name="date" class="form-control border-2" required
                                       value="<?= date('Y-m-d\TH:i', strtotime($event['date_evenement'])) ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Catégorie</label>
                                <select name="categorie_id" class="form-select border-2">
                                    <?php foreach($cats as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $event['categorie_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Lieu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2"><i class="bi bi-geo-alt-fill text-muted"></i></span>
                                    <input type="text" name="lieu" class="form-control border-2" 
                                           value="<?= htmlspecialchars($event['lieu']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Places Max</label>
                                <input type="number" name="nb_max_participants" class="form-control border-2" 
                                       value="<?= htmlspecialchars($event['nb_max_participants']) ?>">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1">Description</label>
                            <textarea name="description" class="form-control border-2" rows="6"><?= htmlspecialchars($event['description']) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold rounded-pill">Annuler</a>
                            <button type="submit" class="btn btn-warning px-5 py-2 fw-bold rounded-pill shadow-sm text-dark">
                                <i class="bi bi-save me-2"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>