<?php
// /admin/create_event.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// 1. Récupérer les catégories pour le menu déroulant
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

// 2. Traitement du formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date'];
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];

    if (!empty($titre) && !empty($date) && !empty($lieu)) {
        $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, categorie_id, nb_max_participants) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max])) {
            // Succès : Redirection vers le dashboard
            header("Location: index.php?msg=created");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de l'enregistrement.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Veuillez remplir les champs obligatoires.</div>";
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Créer un nouvel événement</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?= $message ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre de l'événement *</label>
                            <input type="text" name="titre" class="form-control" required placeholder="Ex: Conférence Tech 2025">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date et Heure *</label>
                                <input type="datetime-local" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <select name="categorie_id" class="form-select">
                                    <?php foreach($cats as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lieu *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="lieu" class="form-control" required placeholder="Ex: Salle de réunion A">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre max de participants</label>
                            <input type="number" name="nb_max_participants" class="form-control" value="50">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description complète</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Détails de l'événement..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success px-5">Enregistrer l'événement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>