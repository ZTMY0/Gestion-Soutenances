<?php
// scripts/fix_accounts.php
require_once '../config/database.php'; // On remonte d'un cran pour trouver config

try {
    // 1. On nettoie les comptes mal créés
    $pdo->exec("DELETE FROM users WHERE role IN ('directeur', 'assistante')");

    // 2. On génère le VRAI hash pour "123456"
    $mdp_hash = password_hash("123456", PASSWORD_DEFAULT);

    // 3. On recrée l'Assistante
    $sqlAssistante = "INSERT INTO users (nom, email, login, password, role) 
                      VALUES ('Mme Assistante', 'assistante@uemf.org', 'assistante.admin', ?, 'assistante')";
    $stmt1 = $pdo->prepare($sqlAssistante);
    $stmt1->execute([$mdp_hash]);

    // 4. On recrée le Directeur
    $sqlDirecteur = "INSERT INTO users (nom, email, login, password, role) 
                     VALUES ('Monsieur le Directeur', 'directeur@uemf.org', 'directeur.general', ?, 'directeur')";
    $stmt2 = $pdo->prepare($sqlDirecteur);
    $stmt2->execute([$mdp_hash]);

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h1 style='color:green'> Comptes réparés avec succès !</h1>";
    echo "<p>Tu peux maintenant te connecter avec le mot de passe <strong>123456</strong>.</p>";
    echo "<hr style='width:300px'>";
    echo "<p> <strong>assistante.admin</strong></p>";
    echo "<p> <strong>directeur.general</strong></p>";
    echo "<br>";
    echo "<a href='../src/views/auth/login.php' style='background:#0d6efd; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Retour à la connexion</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>