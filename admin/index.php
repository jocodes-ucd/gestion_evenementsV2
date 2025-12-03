<?php
// /admin/index.php

// 1. Connexions et Sécurité
require '../includes/db.php';
require 'auth_check.php'; // Vérifie si c'est bien un admin
include '../includes/header.php'; // Charge le design (Bootstrap + Navbar)

// 2. Récupérer la liste de TOUS les événements
// On fait une jointure (LEFT JOIN) pour récupérer le nom de la catégorie au lieu de juste l'ID
$sql = "SELECT e.*, c.nom as cat_nom 
        FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        ORDER BY e.date_evenement DESC";

$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
        <h2 class="mb-0 text-primary">
            <i class="bi bi-speedometer2"></i> Tableau de Bord
        </h2>
        <div>
            <a href="../index.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-eye"></i> Voir le site public
            </a>
            <a href="create_event.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Nouvel Événement
            </a>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 ps-4">Événement</th>
                        <th class="py-3">Date & Lieu</th>
                        <th class="py-3">Catégorie</th>
                        <th class="py-3">Remplissage</th>
                        <th class="py-3 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($events) > 0): ?>
                        <?php foreach($events as $event): ?>
                            <tr>
                                <td class="ps-4 fw-bold align-middle">
                                    <?= htmlspecialchars($event['titre']) ?>
                                </td>
                                
                                <td class="align-middle">
                                    <small class="d-block text-muted">
                                        <i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($event['date_evenement'])) ?>
                                    </small>
                                    <small class="d-block text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['lieu']) ?>
                                    </small>
                                </td>

                                <td class="align-middle">
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($event['cat_nom']) ?>
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <?php 
                                        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
                                        $countStmt->execute([$event['id']]);
                                        $nb_inscrits = $countStmt->fetchColumn();
                                        
                                        // Couleur selon le remplissage
                                        $badgeColor = ($nb_inscrits >= $event['nb_max_participants']) ? 'bg-danger' : 'bg-success';
                                    ?>
                                    <span class="badge <?= $badgeColor ?> rounded-pill">
                                        <?= $nb_inscrits ?> / <?= $event['nb_max_participants'] ?>
                                    </span>
                                </td>

                                <td class="text-end pe-4 align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="participants.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-info text-white" title="Voir les inscrits">
                                            <i class="bi bi-people-fill"></i>
                                        </a>
                                        <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning text-dark" title="Modifier">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="delete_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Toutes les inscriptions seront perdues.');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <h5>Aucun événement trouvé.</h5>
                                <a href="create_event.php" class="btn btn-sm btn-primary mt-2">En créer un maintenant</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>