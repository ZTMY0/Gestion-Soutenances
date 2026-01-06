<?php
session_start();
require_once '../../../config/database.php';

// 1. SÉCURITÉ : Vérifier que c'est bien un Prof
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// 2. TRAITEMENT DU FORMULAIRE
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // A. Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Tous les champs sont obligatoires.";
        $msg_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "La confirmation ne correspond pas au nouveau mot de passe.";
        $msg_type = "danger";
    } elseif (strlen($new_password) < 8) {
        $message = "Le mot de passe doit faire au moins 8 caractères.";
        $msg_type = "warning";
    } else {
        // B. Vérifier l'ancien mot de passe en BDD
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // On vérifie le hash OU le mot de passe en clair (si vieux compte importé)
        $password_ok = password_verify($current_password, $user['password']);
        if (!$password_ok && $current_password === $user['password']) {
            $password_ok = true;
        }

        if ($user && $password_ok) {
            // C. Hachage et Mise à jour
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update->execute([$new_hash, $user_id])) {
                $message = "Votre mot de passe a été modifié avec succès.";
                $msg_type = "success";
            } else {
                $message = "Erreur technique lors de la mise à jour.";
                $msg_type = "danger";
            }
        } else {
            $message = "L'ancien mot de passe est incorrect.";
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Mon Profil | Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 mb-5">
        <div class="container">
            <span class="navbar-brand"><i class="fas fa-chalkboard-teacher me-2"></i>Espace Enseignant</span>
            <div class="d-flex">
                <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-2"></i>Retour Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-user-cog me-2 text-primary"></i>Sécurité du Compte</h5>
                    </div>
                    <div class="card-body p-4">

                        <?php if($message): ?>
                            <div class="alert alert-<?= $msg_type ?> d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-<?= ($msg_type == 'success') ? 'check-circle' : 'exclamation-triangle' ?> me-3 fa-lg"></i>
                                <div><?= $message ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Mot de passe actuel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="current_password" class="form-control border-start-0 bg-light" required>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-key text-primary"></i></span>
                                    <input type="password" name="new_password" class="form-control border-start-0" required minlength="8">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Confirmer le nouveau</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-check-double text-primary"></i></span>
                                    <input type="password" name="confirm_password" class="form-control border-start-0" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="update_password" class="btn btn-primary fw-bold py-2">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>