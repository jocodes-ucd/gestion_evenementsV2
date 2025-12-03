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
    :root{
      --bg:#f6f3ff;
      --ink:#0f172a;
      --muted:#64748b;
      --primaryA:#6d28d9; --primaryB:#db2777; /* Violets/Roses */
      --card:#ffffff;
    }

    body{ background: var(--bg); color: var(--ink); font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
    
    /* NAVIGATION GLASSMORPHISM */
    .nav-glass{
      background: rgba(17, 24, 39, .9); /* Plus foncé pour lisibilité sur toutes pages */
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .nav-link{ color: rgba(255,255,255,.85) !important; font-weight: 600; font-size: 0.95rem; }
    .nav-link:hover{ color: #fff !important; }
    
    .brand-badge{
      width:40px;height:40px;border-radius:12px;
      display:grid;place-items:center;
      background: linear-gradient(135deg, var(--primaryA), var(--primaryB));
      color: white;
    }

    /* ELEMENTS COMMUNS */
    .btn-gradient {
      background: linear-gradient(135deg, var(--primaryA), var(--primaryB));
      color: white; border: none; font-weight: 700;
      transition: transform 0.2s;
    }
    .btn-gradient:hover { transform: translateY(-2px); color: white; box-shadow: 0 10px 20px rgba(109,40,217,.3); }

    .card-custom {
      border: 0; border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.05);
      background: var(--card); overflow: hidden;
    }
    
    /* HERO HEADER (Petit header pour les pages internes) */
    .page-header {
      background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%);
      padding: 80px 0 60px; color: white;
      margin-bottom: -40px; padding-bottom: 80px; /* Effet de chevauchement */
    }

    footer { margin-top: auto; background: white; border-top: 1px solid #eee; padding: 30px 0; }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg nav-glass fixed-top navbar-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="/gestion_evenements/index.php">
      <span class="brand-badge"><i class="bi bi-calendar2-heart-fill"></i></span>
      EventPlace
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="/gestion_evenements/index.php">EXPLORER</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="btn btn-sm btn-warning fw-bold" href="/gestion_evenements/admin/index.php">ADMIN</a></li>
            <?php endif; ?>
            <li class="nav-item ms-2">
               <a class="nav-link text-danger" href="/gestion_evenements/logout.php"><i class="bi bi-box-arrow-right"></i></a>
            </li>
        <?php else: ?>
            <li class="nav-item ms-2">
               <a class="btn btn-outline-light btn-sm fw-bold px-3 rounded-pill" href="/gestion_evenements/login.php">CONNEXION</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div style="height: 70px;"></div>