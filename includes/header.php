<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    /* --- DESIGN SYSTEM GLOBAL --- */
    :root{
      --bg:#f6f3ff;
      --ink:#0f172a;
      --muted:#64748b;
      --primaryA:#6d28d9; /* Violet */
      --primaryB:#db2777; /* Rose */
      --card:#ffffff;
    }

    body{ 
        background: var(--bg); 
        color: var(--ink); 
        font-family: 'Segoe UI', sans-serif; 
        min-height: 100vh; 
        display: flex; 
        flex-direction: column; 
    }
    
    /* NAVIGATION "GLASSMORPHISM" (Fonctionne sur fond clair et foncé) */
    .nav-glass{
      background: rgba(15, 23, 42, 0.95); /* Fond sombre presque opaque pour lisibilité */
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,.1);
      padding: 12px 0;
    }
    .nav-link{ 
        color: rgba(255,255,255,.8) !important; 
        font-weight: 600; 
        font-size: 0.9rem;
        transition: 0.3s; 
    }
    .nav-link:hover{ color: #fff !important; }
    
    /* LOGO CARRÉ */
    .brand-badge{
      width:38px; height:38px; border-radius:10px;
      display:grid; place-items:center;
      background: linear-gradient(135deg, var(--primaryA), var(--primaryB));
      color: white; font-size: 1.1rem;
      box-shadow: 0 4px 12px rgba(109,40,217,0.3);
    }

    /* CARTES MODERNES (Pour Login, Admin, Details) */
    .card-custom {
      border: 0; border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.05);
      background: var(--card); overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    /* BOUTONS DÉGRADÉS */
    .btn-gradient {
      background: linear-gradient(135deg, var(--primaryA), var(--primaryB));
      color: white; border: none; font-weight: 700;
      transition: 0.2s;
    }
    .btn-gradient:hover { transform: translateY(-2px); color: white; box-shadow: 0 10px 20px rgba(109,40,217,.3); }

    /* PETIT HEADER (Pour les pages autres que l'accueil) */
    .page-header {
      background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%);
      padding: 100px 0 60px; color: white;
      margin-top: -80px; /* Pour passer sous la navbar fixe */
      padding-bottom: 80px;
      margin-bottom: -40px;
    }

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
        <li class="nav-item"><a class="nav-link" href="/gestion_evenements/index.php">ACCUEIL</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="btn btn-sm btn-warning fw-bold text-dark px-3 rounded-pill" href="/gestion_evenements/admin/index.php">
                        <i class="bi bi-gear-fill"></i> ADMIN
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nom']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                    <li><a class="dropdown-item text-danger fw-bold" href="/gestion_evenements/logout.php">Se déconnecter</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item ms-lg-2">
               <a class="btn btn-outline-light btn-sm fw-bold px-4 rounded-pill" href="/gestion_evenements/login.php">CONNEXION</a>
            </li>
            <li class="nav-item">
               <a class="btn btn-light btn-sm fw-bold px-4 rounded-pill text-primary" href="/gestion_evenements/register.php">S'INSCRIRE</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div style="height: 80px;"></div>