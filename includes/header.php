<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


// --- LOGIQUE NOTIFICATIONS ---
$unreadCount = 0; $popupNotifs = [];
if (isset($_SESSION['user_id']) && isset($pdo)) {
    // 1. Compter non-lues
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtCount->execute([$_SESSION['user_id']]);
    $unreadCount = $stmtCount->fetchColumn();

    // 2. Récupérer pour popup
    $stmtPop = $pdo->prepare("SELECT n.*, e.titre as event_titre FROM notifications n LEFT JOIN evenements e ON n.evenement_id = e.id WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT 5");
    $stmtPop->execute([$_SESSION['user_id']]);
    $popupNotifs = $stmtPop->fetchAll();
    $currentUrl = basename($_SERVER['PHP_SELF']) . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>EventPlace — Interne</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    :root{ --bg:#f6f3ff; --ink:#0f172a; --primaryA:#6d28d9; --primaryB:#db2777; --card:#ffffff; }
    body{ background: var(--bg); color: var(--ink); font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
    
    .nav-glass{ background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,.1); padding: 10px 0; }
    .nav-link{ color: rgba(255,255,255,.8) !important; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
    .nav-link:hover{ color: #fff !important; }
    .brand-badge{ width:38px; height:38px; border-radius:10px; display:grid; place-items:center; background: linear-gradient(135deg, var(--primaryA), var(--primaryB)); color: white; font-size: 1.1rem; }

    /* AVATAR DANS LA NAVBAR (Harmonie avec la cloche) */
    .nav-avatar {
        width: 42px; height: 42px; /* Un peu plus grand que la cloche */
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.2);
        transition: 0.2s;
    }
    .nav-avatar:hover { border-color: white; transform: scale(1.05); }

    /* Styles Popup Notif (déjà vu précédemment) */
    .notif-dropdown { width: 380px; padding: 0; border-radius: 16px; border: 0; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; }
    .notif-header { background: #f8f9fa; padding: 12px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .notif-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; display: flex; gap: 15px; align-items: start; transition: 0.2s; position: relative; }
    .notif-item:hover { background: #f9faff; }
    .notif-item.unread { background: #fff; border-left: 4px solid var(--primaryB); }
    .notif-icon { width: 36px; height: 36px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .notif-content { flex-grow: 1; font-size: 0.9rem; line-height: 1.4; }
    .btn-mark-read { color: #ccc; transition: 0.2s; cursor: pointer; }
    .btn-mark-read:hover { color: var(--primaryA); }
    .notif-footer { text-align: center; padding: 12px; background: #f8f9fa; font-size: 0.85rem; font-weight: bold; }
    .notif-footer a { text-decoration: none; color: var(--primaryA); }
    
    .card-custom { border: 0; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); background: var(--card); overflow: hidden; }
    .btn-gradient { background: linear-gradient(135deg, var(--primaryA), var(--primaryB)); color: white; border: none; font-weight: 700; transition: 0.2s; }
    .btn-gradient:hover { transform: translateY(-2px); color: white; }
    .page-header { background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%); padding: 100px 0 60px; color: white; margin-top: -80px; padding-bottom: 80px; margin-bottom: -40px; }
    footer { margin-top: auto; background: white; border-top: 1px solid #eee; padding: 30px 0; }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg nav-glass fixed-top navbar-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white" href="/gestion_evenements/index.php">
      <span class="brand-badge"><i class="bi bi-calendar2-heart-fill"></i></span>
      EventPlace
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        
        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
            <!-- ADMIN NAVIGATION -->
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/admin/index.php"><i class="bi bi-speedometer2 me-1"></i>TABLEAU DE BORD</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/admin/users.php"><i class="bi bi-people me-1"></i>UTILISATEURS</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/admin/create_event.php"><i class="bi bi-plus-circle me-1"></i>CRÉER ÉVÉNEMENT</a></li>
        <?php elseif(isset($_SESSION['user_id'])): ?>
            <!-- REGULAR USER NAVIGATION -->
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/index.php">ACCUEIL</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/mon_espace.php"></i>MON ESPACE</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/events.php">AGENDA</a></li>
        <?php else: ?>
            <!-- NOT LOGGED IN NAVIGATION -->
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/index.php">ACCUEIL</a></li>
            <li class="nav-item"><a class="nav-link" href="/gestion_evenements/events.php">AGENDA</a></li>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            
            <li class="nav-item dropdown me-3">
                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if($unreadCount > 0): ?>
                        <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger border border-light border-2" style="font-size: 0.6rem;">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-header">
                        <span class="fw-bold small text-muted">Notifications</span>
                        <?php if($unreadCount > 0): ?>
                            <a href="notifications_action.php?read_all=1&redirect=<?= urlencode($currentUrl) ?>" class="small text-decoration-none text-primary">Tout marquer comme lu</a>
                        <?php endif; ?>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if(count($popupNotifs) > 0): ?>
                            <?php foreach($popupNotifs as $n): ?>
                                <li class="notif-item <?= $n['is_read'] ? 'read' : 'unread' ?>">
                                    <div class="notif-icon text-primary"><i class="bi bi-info-circle-fill"></i></div>
                                    <div class="notif-content">
                                        <div><?= htmlspecialchars($n['message']) ?></div>
                                        <span class="notif-time"><?= date('d/m H:i', strtotime($n['created_at'])) ?></span>
                                    </div>
                                    <?php if(!$n['is_read']): ?>
                                        <a href="notifications_action.php?read=<?= $n['id'] ?>&redirect=<?= urlencode($currentUrl) ?>" class="btn-mark-read"><i class="bi bi-check-circle-fill"></i></a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="p-4 text-center text-muted small">Aucune notification.</li>
                        <?php endif; ?>
                    </div>
                    <div class="notif-footer"><a href="notifications.php">Voir tout</a></div>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown">
                    <?php 
                        // --- CORRECTION DU CHEMIN D'IMAGE ---
                        if (!empty($_SESSION['photo_profil'])) {
                            // On ajoute le dossier racine pour que l'image soit visible depuis l'Admin aussi
                            $avatarUrl = '/gestion_evenements/' . $_SESSION['photo_profil'];
                        } else {
                            // Avatar par défaut si pas de photo
                            $avatarUrl = 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['nom']).'&background=random';
                        }
                    ?>
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" class="nav-avatar">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                    <li class="px-3 py-2 text-muted small fw-bold">
                        <?= $_SESSION['role'] === 'admin' ? '👑 Admin' : 'Compte' ?><br>
                        <span class="text-dark"><?= htmlspecialchars($_SESSION['nom']) ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li><a class="dropdown-item fw-bold" href="/gestion_evenements/index.php"><i class="bi bi-eye me-2"></i>Voir le Site Public</a></li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    
                    <li><a class="dropdown-item fw-bold" href="/gestion_evenements/profile.php"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="/gestion_evenements/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </li>

        <?php else: ?>
            <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm fw-bold px-4 rounded-pill" href="/gestion_evenements/login.php">CONNEXION</a></li>
            <li class="nav-item"><a class="btn btn-light btn-sm fw-bold px-4 rounded-pill text-primary" href="/gestion_evenements/register.php">S'INSCRIRE</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div style="height: 80px;"></div>