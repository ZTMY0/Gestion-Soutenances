<?php
// reset_admin.php est à la racine, donc on pointe vers config/database.php
// Si ça échoue encore, essaie : require_once 'src/config/database.php';
require_once '../config/database.php'; 

try {
    // 1. Supprime l'ancien coordinateur
    $pdo->query("DELETE FROM users WHERE role = 'coordinateur'");

    // 2. Hash du mot de passe "123456"
    $password = password_hash("123456", PASSWORD_DEFAULT);

    // 3. Création du compte Admin
    // On met filiere_id à NULL car l'admin gère tout
    $sql = "INSERT INTO users (nom, email, login, password, role, filiere_id) 
            VALUES ('Ihab Admin', 'ihab@admin.com', 'ihab.admin', ?, 'coordinateur', NULL)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$password]);

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h1 style='color:green'> Compte Admin (Coordinateur) recréé !</h1>";
    echo "<hr style='width:300px'>";
    echo "<p>Login : <strong>ihab.admin</strong></p>";
    echo "<p>Pass : <strong>123456</strong></p>";
    echo "<br>";
    echo "<a href='src/views/auth/login.php' style='background:#0d6efd; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Aller à la connexion</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Erreur SQL : " . $e->getMessage() . "</h3>");
} catch (Error $e) {
    die("<h3 style='color:red'>Erreur de Fichier : " . $e->getMessage() . "</h3><p>Vérifie que le fichier <code>config/database.php</code> existe bien à cet endroit.</p>");
}
?>