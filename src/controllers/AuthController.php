<?php
session_start();
require_once __DIR__ . '/../../config/database.php'; 

// --- TRAITEMENT DU LOGIN ---
if (isset($_POST['login_btn'])) {
    
    // On nettoie les entrées
    $input_id = trim($_POST['identifiant']); 
    $password = $_POST['password'];

    if (!empty($input_id) && !empty($password)) {
        
        try {
            // 1. REQUÊTE INTELLIGENTE : Cherche par Email OU Login
            // Ex: marche avec "ihab.zaghdane" OU "ihab.zaghdane@eidia.ueuromed.org"
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :input OR login = :input LIMIT 1");
            $stmt->execute(['input' => $input_id]);
            $user = $stmt->fetch();

            $auth_success = false;

            if ($user) {
                // --- C'EST ICI LA MAGIE ---
                
                // A. Test Mot de passe EN CLAIR (Pour votre CSV actuel : T6YaPbNd...)
                if ($password === $user['password']) {
                    $auth_success = true;
                    
                    // Optionnel : On pourrait le crypter maintenant pour la prochaine fois
                    // mais on garde simple pour la démo.
                }
                // B. Test Mot de passe HACHÉ (Pour la sécurité standard : $2y$10$...)
                elseif (password_verify($password, $user['password'])) {
                    $auth_success = true;
                }
            }

            // 3. SI CONNEXION RÉUSSIE
            if ($auth_success) {
                
                // Création de la Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['filiere_id'] = $user['filiere_id'] ?? null;

                // Redirection selon le rôle
                switch ($user['role']) {
                    case 'etudiant': header("Location: ../views/etudiant/index.php"); break;
                    case 'prof': header("Location: ../views/prof/index.php"); break;
                    case 'coordinateur': header("Location: ../views/coordinateur/index.php"); break;
                    case 'directeur': header("Location: ../views/directeur/index.php"); break;
                    case 'assistante': header("Location: ../views/assistante/index.php"); break;
                    default: 
                        session_destroy();
                        header("Location: ../views/auth/login.php?error=role_inconnu");
                }
                exit();

            } else {
                // Échec Mot de passe
                header("Location: ../views/auth/login.php?error=bad_credentials&login=" . urlencode($input_id));
                exit();
            }

        } catch (PDOException $e) {
            header("Location: ../views/auth/login.php?error=system_error");
            exit();
        }
    } else {
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