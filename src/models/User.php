<?php
// src/models/User.php
require_once __DIR__ . '/../../config/database.php';

function checkCredentials($email, $password) {
    global $pdo;
    
    // On récupère tout, y compris la filière pour le coordinateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // NOTE: Pour le MVP on compare en clair.
    // Si tu veux faire pro : if ($user && password_verify($password, $user['password']))
    if ($user && $password === $user['password']) {
        return $user;
    }
    return false;
}
?>