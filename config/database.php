<?php

$host = 'sql108.infinityfree.com'; 
$db   = 'if0_40832873_soutenances';
$user = 'if0_40832873';   
$pass = 'EiQLDZvsHeRg';   
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";


$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // CRUCIAL : Désactiver la persistance pour éviter le "Connection timed out"
    PDO::ATTR_PERSISTENT         => false, 
    // Timeout de connexion réduit pour ne pas faire attendre l'utilisateur 60s
    PDO::ATTR_TIMEOUT            => 10, 
];

// Tentative de connexion avec "Retry" automatique (3 essais)
$max_retries = 3;
$attempts = 0;
$connected = false;

while ($attempts < $max_retries && !$connected) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $connected = true;
    } catch (\PDOException $e) {
        $attempts++;
        // Si c'est la dernière tentative, on arrête et on affiche l'erreur
        if ($attempts === $max_retries) {
            // On affiche un message propre au lieu de l'erreur technique SQL
            die("Erreur de connexion (Serveur saturé). Veuillez rafraîchir la page.");
        }
        // Sinon, on attend 1 seconde avant de réessayer (le temps que le serveur respire)
        sleep(1);
    }
}
?>