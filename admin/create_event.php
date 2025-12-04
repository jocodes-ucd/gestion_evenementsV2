<?php
// admin/create_event.php
require '../includes/db.php';
require 'auth_check.php';

// 1. LOGIQUE PHP (Toujours au début)
$message = "";
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date'];
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];
    
    // Upload Image
    $imagePath = NULL;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
            $newName = "event_" . time() . "." . $ext;
            $target = "uploads/events/" . $newName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], "../" . $target)) {
                $imagePath = $target;
            }
        }
    }

    if (!empty($titre) && !empty($date) && !empty($lieu)) {
        $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, categorie_id, nb_max_participants, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max, $imagePath])) {
            header("Location: index.php?msg=created");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur SQL.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Champs manquants.</div>";
    }
}

// 2. DESIGN
include '../includes/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                
                <div class="card-header bg-primary text-white p-4">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill me-2"></i>Créer un événement</h4>
                    <p class="mb-0 opacity-75 small">Remplissez les détails ci-dessous</p>
                </div>

                <div class="card-body p-5 bg-white">
                    
                    <?= $message ?>

                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1">Titre de l'événement <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control form-control-lg border-2" placeholder="Ex: Soirée de lancement..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1">Image de couverture</label>
                            <input type="file" name="image" class="form-control border-2" accept="image/*">
                            <div class="form-text">Format JPG/PNG. Max 5Mo.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Date & Heure <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="date" class="form-control border-2" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Catégorie <span class="text-danger">*</span></label>
                                <select name="categorie_id" class="form-select border-2">
                                    <?php foreach($cats as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Lieu <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2"><i class="bi bi-geo-alt-fill text-muted"></i></span>
                                    <input type="text" name="lieu" class="form-control border-2" placeholder="Ex: Salle de réunion A" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold text-dark text-uppercase small ls-1">Places Max</label>
                                <input type="number" name="nb_max_participants" class="form-control border-2" value="50">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-bold text-dark text-uppercase small ls-1">Description</label>
                            <textarea name="description" class="form-control border-2" rows="6" placeholder="Détails, programme, intervenants..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold rounded-pill">Annuler</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow">
                                <i class="bi bi-check-lg me-2"></i> Publier l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>