<?php
// Fichier : /includes/db.php

$host = 'localhost';
$dbname = 'event_system';
$username = 'root'; // Utilisateur par défaut de XAMPP
$password = '';     // Mot de passe vide par défaut sur XAMPP

try {
    // On utilise PDO pour la sécurité (Protection contre les injections SQL)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Activer les erreurs pour voir les problèmes (Indispensable en dev)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}
?>