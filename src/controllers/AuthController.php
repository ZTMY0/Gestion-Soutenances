<?php
session_start();

// On inclut la connexion BDD directement
require_once __DIR__ . '/../../config/database.php'; 

// LOGIN
if (isset($_POST['login_btn'])) {
    
    // 1. On récupère le LOGIN (prenom.nom)
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (!empty($login) && !empty($password)) {
        
        // 2. Requete SQL par 'login'
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        // 3. Vérification du mot de passe haché
        if ($user && password_verify($password, $user['password'])) {
            
            // --- CRÉATION DE LA SESSION "ROYALE" ---
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            // On gère le cas où filiere_id peut être NULL (Directeur/Assistante)
            $_SESSION['filiere_id'] = $user['filiere_id'] ?? null; 

            // --- REDIRECTION INTELLIGENTE ---
            switch ($user['role']) {
                case 'coordinateur':
                    header("Location: ../views/coordinateur/index.php");
                    break;
                case 'prof':
                    header("Location: ../views/prof/index.php");
                    break;
                case 'etudiant':
                    header("Location: ../views/etudiant/index.php");
                    break;
                case 'directeur':
                    header("Location: ../views/directeur/index.php");
                    break;
                case 'assistante': // <--- AJOUT CRUCIAL ICI
                    header("Location: ../views/assistante/index.php");
                    break;
                default:
                    // Rôle non reconnu
                    header("Location: ../views/auth/login.php?error=role_inconnu");
            }
            exit();

        } else {
            // Mauvais mot de passe ou login incorrect
            header("Location: ../views/auth/login.php?error=bad_credentials&login=" . urlencode($login));
            exit();
        }
    } else {
        // Champs vides
        header("Location: ../views/auth/login.php?error=empty");
        exit();
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../views/auth/login.php");
    exit();
}
?>