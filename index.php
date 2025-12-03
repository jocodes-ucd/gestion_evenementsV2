<?php
// index.php
// 1. Connexion BDD et Session
require 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Récupérer les événements
$sql = "SELECT e.*, c.nom as categorie_nom 
        FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        ORDER BY e.date_evenement ASC";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<style>
    /* HERO: Le grand fond violet/rose spécifique à l'accueil */
    .hero {
        position: relative; overflow: hidden; min-height: 580px;
        color: #fff; margin-top: -70px; /* Remonte sous la navbar */
        padding-top: 100px;
        background:
            radial-gradient(900px 500px at 15% 30%, rgba(219,39,119,.55), transparent 60%),
            radial-gradient(900px 550px at 70% 20%, rgba(109,40,217,.65), transparent 65%),
            linear-gradient(135deg, #2e1065 0%, #4c1d95 35%, #6d28d9 65%, #db2777 120%);
    }
    /* Cercles décoratifs */
    .hero::before {
        content:""; position:absolute; inset:-200px -200px auto auto;
        width: 700px; height: 700px; border-radius: 999px;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.22), transparent 55%);
        transform: rotate(12deg);
    }
    
    /* SEARCH BAR: La barre blanche qui chevauche le hero */
    .search-wrap { margin-top: -50px; position: relative; z-index: 10; padding-bottom: 60px; }
    .search-card {
        background: rgba(255,255,255,.95); backdrop-filter: blur(10px);
        border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,.15);
        padding: 20px; border: 1px solid rgba(255,255,255,0.5);
    }

    /* Images circulaires */
    .avatar-ring {
        position:absolute; border-radius:50%; border: 8px solid rgba(255,255,255,0.2);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow:hidden;
    }
    .avatar-ring img { width:100%; height:100%; object-fit: cover; }
</style>

<header class="hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 position-relative" style="z-index: 2;">
                <div class="fw-bold text-warning mb-2" style="letter-spacing: 2px;">INTERNE</div>
                <h1 class="display-3 fw-bold mb-4">
                    Découvrir & organiser<br/>
                    vos <span style="color: #ffb4f0;">événements</span>
                </h1>
                <p class="lead mb-0 text-white opacity-75" style="max-width: 500px;">
                    Réunions, formations, team building... Centralisez la gestion de vos événements d'entreprise en un seul endroit sécurisé.
                </p>
            </div>

            <div class="col-lg-5 position-relative d-none d-lg-block" style="height: 400px;">
                <div class="avatar-ring" style="width:260px; height:260px; right:0; top:20px;">
                    <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?w=600&q=80" alt="Conférence">
                </div>
                <div class="avatar-ring" style="width:160px; height:160px; right:240px; top:180px;">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&q=80" alt="Meeting">
                </div>
            </div>
        </div>
    </div>
</header>

<section class="search-wrap">
    <div class="container">
        <div class="search-card">
            <form class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 bg-transparent shadow-none" placeholder="Rechercher un événement (ex: Marketing)...">
                    </div>
                </div>
                <div class="col-lg-3 border-start">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="bi bi-geo-alt text-muted"></i></span>
                        <select class="form-select border-0 bg-transparent shadow-none text-muted">
                            <option selected>Tous les lieux</option>
                            <option>Salle A</option>
                            <option>Salle B</option>
                            <option>Auditorium</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <button type="button" class="btn btn-gradient w-100 py-3 rounded-3 shadow-sm">
                        Trouver mon événement
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<main class="pb-5">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5">
            <div>
                <h6 class="text-primary fw-bold text-uppercase ls-1">Agenda</h6>
                <h2 class="fw-bold display-6">Prochains Événements</h2>
            </div>
            <a href="#" class="btn btn-outline-dark rounded-pill px-4 d-none d-md-block">Voir tout</a>
        </div>

        <div class="row g-4">
            <?php if(count($events) > 0): ?>
                <?php foreach($events as $event): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-custom h-100 position-relative group-hover">
                            <?php 
                                $img = "https://images.unsplash.com/photo-1475721027767-9662938a5a63?w=800"; // Défaut
                                if(stripos($event['categorie_nom'], 'conf') !== false) $img = "https://images.unsplash.com/photo-1544531586-fde5298cdd40?w=800";
                                if(stripos($event['categorie_nom'], 'atelier') !== false) $img = "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800";
                                if(stripos($event['categorie_nom'], 'réunion') !== false) $img = "https://images.unsplash.com/photo-1577962917302-cd874c4e3169?w=800";
                            ?>
                            <div style="height: 220px; overflow: hidden;">
                                <img src="<?= $img ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.5s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                        <?= htmlspecialchars($event['categorie_nom']) ?>
                                    </span>
                                    <small class="text-muted fw-bold">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d M', strtotime($event['date_evenement'])) ?>
                                    </small>
                                </div>

                                <h4 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($event['titre']) ?></h4>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                    <?= htmlspecialchars($event['lieu']) ?>
                                </div>

                                <p class="text-secondary small flex-grow-1">
                                    <?= substr(htmlspecialchars($event['description']), 0, 90) ?>...
                                </p>

                                <div class="d-grid mt-4">
                                    <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-outline-dark fw-bold rounded-3 py-2">
                                        Réserver ma place
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-light shadow-sm d-inline-block px-5 py-4">
                        <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
                        <h4 class="text-muted">Aucun événement trouvé</h4>
                        <p class="mb-0">Revenez plus tard pour de nouvelles dates.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>