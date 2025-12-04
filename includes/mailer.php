<?php
// /includes/mailer.php

// Chargement manuel des fichiers qu'on vient de télécharger
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURATION SERVEUR ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // 👇👇👇 TES INFOS ICI 👇👇👇
        $mail->Username   = 'yousseflad9@gmail.com'; 
        $mail->Password   = 'kpqt ylzf gren prmv'; // Les 16 lettres sans espaces
        // 👆👆👆👆👆👆👆👆👆👆👆👆👆

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement standard
        $mail->Port       = 587; // Port standard Gmail

        // --- EXPÉDITEUR ---
        $mail->setFrom($mail->Username, 'EventPlace Admin'); // L'expéditeur est ton compte Gmail
        $mail->addAddress($to); // Le destinataire est l'utilisateur qui s'inscrit

        // --- CONTENU ---
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8'; // Important pour les accents
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body); // Transforme les \n en <br> pour le HTML
        $mail->AltBody = strip_tags($body); // Version texte brut pour les vieux clients mail

        $mail->send();
        return true;

    } catch (Exception $e) {
        // En mode dev, tu peux décommenter la ligne suivante pour voir l'erreur exacte
        // echo "Erreur Mailer: {$mail->ErrorInfo}";
        
        // On log l'erreur dans un fichier pour le débogage silencieux
        file_put_contents('../logs_erreurs_mail.txt', date('Y-m-d H:i:s') . " - Erreur: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
}
?>