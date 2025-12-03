<?php
// index.php
require 'includes/db.php';
// On inclut le header qui contient déjà la session_start et la Navbar moderne
include 'includes/header.php';

// Récupérer les événements
$sql = "SELECT e.*, c.nom as categorie_nom 
        FROM evenements e 
        LEFT JOIN categories c ON e.categorie_id = c.id 
        ORDER BY e.date_evenement ASC";
$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<style>
    /* On cache le spacer standard car le Hero doit passer DESSOUS la navbar */
    body > div[style="height: 80px;"] { display: none; }

    .hero {
        position: relative; overflow: hidden; min-height: 600px;
        color: #fff; 
        padding-top: 120px; /* Pour compenser la navbar fixe */
        background:
            radial-gradient(900px 500px at 15% 30%, rgba(219,39,119,.55), transparent 60%),
            radial-gradient(900px 550px at 70% 20%, rgba(109,40,217,.65), transparent 65%),
            linear-gradient(135deg, #2e1065 0%, #4c1d95 35%, #6d28d9 65%, #db2777 120%);
    }
    .hero::before {
        content:""; position:absolute; inset:-200px -200px auto auto;
        width: 700px; height: 700px; border-radius: 999px;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.22), transparent 55%);
        transform: rotate(12deg);
    }
    .avatar-ring {
        position:absolute; border-radius:50%; border: 8px solid rgba(255,255,255,0.2);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow:hidden;
    }
</style>

<header class="hero">
    <div class="container pb-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 position-relative" style="z-index: 2;">
                <div class="badge bg-white bg-opacity-25 text-white mb-3 px-3 py-2 rounded-pill">🎉 PLATEFORME INTERNE</div>
                <h1 class="display-3 fw-bold mb-4">
                    Gérez vos événements<br/>
                    <span style="color: #ffb4f0;">simplement.</span>
                </h1>
                <p class="lead mb-4 text-white opacity-75" style="max-width: 500px;">
                    Réunions, formations, team building... Tout est centralisé ici.
                    Inscrivez-vous en un clic et suivez vos réservations.
                </p>
                <div class="d-flex gap-3">
                    <a href="#agenda" class="btn btn-light fw-bold px-4 py-3 rounded-pill text-primary shadow-lg">Voir l'agenda</a>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-outline-light fw-bold px-4 py-3 rounded-pill">Créer un compte</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5 position-relative d-none d-lg-block" style="height: 400px;">
                <div class="avatar-ring" style="width:280px; height:280px; right:0; top:20px;">
                    <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?w=600&q=80" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="avatar-ring" style="width:180px; height:180px; right:240px; top:200px; border-width:6px;">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&q=80" style="width:100%;height:100%;object-fit:cover;">
                </div>
            </div>
        </div>
    </div>
</header>

<main class="py-5" id="agenda">
    <div class="container">
        <div class="card card-custom p-4 mb-5" style="margin-top: -80px; position: relative; z-index: 10;">
            <form class="row g-3">
                 <div class="col-md-5">
                     <input type="text" class="form-control border-0 bg-light py-3 rounded-3" placeholder="🔍 Rechercher un événement...">
                 </div>
                 <div class="col-md-4">
                     <select class="form-select border-0 bg-light py-3 rounded-3 text-muted">
                         <option>Toutes les catégories</option>
                         <option>Conférence</option>
                         <option>Atelier</option>
                     </select>
                 </div>
                 <div class="col-md-3">
                     <button class="btn btn-gradient w-100 py-3 rounded-3">Filtrer</button>
                 </div>
            </form>
        </div>

        <div class="d-flex align-items-end justify-content-between mb-4">
            <h2 class="fw-bold display-6">📅 Prochains Événements</h2>
        </div>

        <div class="row g-4">
            <?php if(count($events) > 0): ?>
                <?php foreach($events as $event): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-custom h-100 group-hover">
                            <div style="height:220px; overflow: hidden; position: relative;">
                                <?php 
                                    $img = "https://images.unsplash.com/photo-1475721027767-9662938a5a63?w=800"; 
                                    if(stripos($event['categorie_nom'], 'conf') !== false) $img = "https://images.unsplash.com/photo-1544531586-fde5298cdd40?w=800";
                                    if(stripos($event['categorie_nom'], 'atelier') !== false) $img = "https://images.unsplash.com/photo-1552664730-d307ca884978?w=800";
                                ?>
                                <img src="<?= $img ?>" style="width:100%; height:100%; object-fit:cover; transition:transform 0.5s;" 
                                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                <div class="position-absolute top-0 start-0 m-3 badge bg-white text-dark fw-bold shadow-sm">
                                    <?= htmlspecialchars($event['categorie_nom']) ?>
                                </div>
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="text-muted small fw-bold mb-2">
                                    <i class="bi bi-calendar-check me-1 text-primary"></i>
                                    <?= date('d F Y', strtotime($event['date_evenement'])) ?>
                                </div>
                                <h4 class="fw-bold mb-2"><?= htmlspecialchars($event['titre']) ?></h4>
                                <div class="text-secondary small mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($event['lieu']) ?>
                                </div>
                                <p class="text-muted small flex-grow-1">
                                    <?= substr(htmlspecialchars($event['description']), 0, 90) ?>...
                                </p>
                                <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-outline-dark fw-bold rounded-3 mt-3">
                                    Réserver ma place
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-light d-inline-block px-5 shadow-sm">
                        <h4>Aucun événement trouvé 😴</h4>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>