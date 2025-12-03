<?php
// /includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Événements</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="/gestion_evenements/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 shadow-sm" style="background: linear-gradient(to right, #2c3e50, #4ca1af);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/gestion_evenements/index.php">
        <i class="bi bi-calendar-event"></i> EventManager
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <a class="nav-link text-white" href="/gestion_evenements/index.php">Explorer</a>
        </li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item ms-2">
                    <a class="btn btn-sm btn-warning fw-bold text-dark" href="/gestion_evenements/admin/index.php">
                        <i class="bi bi-gear-fill"></i> Admin
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item ms-3">
                <a class="nav-link text-white-50" href="/gestion_evenements/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item ms-3">
                <a class="btn btn-outline-light rounded-pill px-4" href="/gestion_evenements/login.php">Connexion</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">