<?php
// config/session_check.php

// 1. Configuration de la session (Sécurité Cookies)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // Empêche le vol de cookie par JavaScript (XSS)
    ini_set('session.use_strict_mode', 1); // Empêche la fixation de session
    session_start();
}

// 2. Timeout (Déconnexion auto après 15min)
$timeout_duration = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

// 3. GÉNÉRATION DU JETON CSRF 
if (empty($_SESSION['csrf_token'])) {
    // On génère une chaîne aléatoire cryptographique de 64 caractères
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>