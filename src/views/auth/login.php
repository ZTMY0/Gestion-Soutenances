<?php
session_start();
require_once '../../../config/database.php';

// Vérification de la présence du module 2FA
$otp_enabled = file_exists('../../../src/Services/GoogleAuthenticator.php');
if ($otp_enabled) {
    require_once '../../../src/Services/GoogleAuthenticator.php';
}

$error = "";
$step = 1;

if (isset($_SESSION['otp_pending_user_id'])) {
    $step = 2;
}

// --- ÉTAPE 1 : TRAITEMENT DU LOGIN ---
if (isset($_POST['login_btn'])) {
    $identifiant = trim($_POST['identifiant']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR login = ?");
    $stmt->execute([$identifiant, $identifiant]);
    $user = $stmt->fetch();

    if ($user) {
        $password_ok = password_verify($password, $user['password']);
        if (!$password_ok && $password === $user['password']) {
            $password_ok = true;
        }

        if ($password_ok) {
            // Gestion OTP
            if ($otp_enabled && !empty($user['otp_secret'])) {
                $_SESSION['otp_pending_user_id'] = $user['id'];
                $_SESSION['otp_pending_role'] = $user['role'];
                $_SESSION['otp_pending_nom'] = $user['nom'];
                $_SESSION['otp_pending_secret'] = $user['otp_secret'];
                $step = 2;
            } else {
                // Connexion directe
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom']; 
                
                // --- REDIRECTIONS PAR RÔLE ---
                if ($user['role'] == 'etudiant') {
                    header("Location: ../etudiant/index.php");
                } elseif ($user['role'] == 'coordinateur') {
                    header("Location: ../coordinateur/index.php");
                } elseif ($user['role'] == 'prof') {
                    header("Location: ../prof/index.php");
                } elseif ($user['role'] == 'directeur' || $user['role'] == 'admin') {
                    header("Location: ../directeur/index.php");
                } elseif ($user['role'] == 'assistante') { 
                    // AJOUT DU CAS ASSISTANTE
                    header("Location: ../assistante/index.php");
                } else {
                    die("Erreur : Rôle inconnu (" . $user['role'] . ")");
                }
                exit();
            }
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        $error = "Identifiant ou mot de passe incorrect.";
    }
}

// --- ÉTAPE 2 : VÉRIFICATION OTP ---
if (isset($_POST['otp_verify_btn']) && $otp_enabled) {
    $code = $_POST['otp_code'];
    $secret = $_SESSION['otp_pending_secret'];
    
    $ga = new GoogleAuthenticator();
    if ($ga->verifyCode($secret, $code, 1)) {
        $_SESSION['user_id'] = $_SESSION['otp_pending_user_id'];
        $_SESSION['user_role'] = $_SESSION['otp_pending_role'];
        $_SESSION['user_nom'] = $_SESSION['otp_pending_nom'];
        
        $role = $_SESSION['user_role'];

        unset($_SESSION['otp_pending_user_id']);
        unset($_SESSION['otp_pending_secret']);
        unset($_SESSION['otp_pending_role']);
        unset($_SESSION['otp_pending_nom']);

        // --- REDIRECTIONS PAR RÔLE (OTP) ---
        if ($role == 'etudiant') {
            header("Location: ../etudiant/index.php");
        } elseif ($role == 'coordinateur') {
            header("Location: ../coordinateur/index.php");
        } elseif ($role == 'prof') {
            header("Location: ../prof/index.php");
        } elseif ($role == 'directeur' || $role == 'admin') {
            header("Location: ../directeur/index.php");
        } elseif ($role == 'assistante') {
            // AJOUT DU CAS ASSISTANTE
            header("Location: ../assistante/index.php");
        }
        exit();
    } else {
        $error = "Code 2FA incorrect.";
        $step = 2;
    }
}

if (isset($_GET['logout']) && $_GET['logout'] == 'temp') {
    unset($_SESSION['otp_pending_user_id']);
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <style> .letter-spacing-2 { letter-spacing: 5px; font-weight: bold; } </style>
</head>
<body class="login-page">
    <div class="card login-card">
        <div class="card-header-custom">
            <img src="../../../public/assets/img/logo_uemf.jpeg" alt="Logo UEMF" class="uemf-logo">
            <h5 class="fw-bold text-dark mb-0 mt-2">Espace PFE</h5>
            <p class="text-muted small mb-0">Université Euromed de Fès</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2 fs-6 shadow-sm mb-4 border-0 rounded-1">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Identifiant</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="identifiant" class="form-control" required placeholder="Login académique ou Email" autofocus>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-secondary">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                    </div>
                    <div class="text-end mb-4">
                        <a href="#" class="text-decoration-none small text-muted" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Mot de passe oublié ?</a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="login_btn" class="btn btn-primary-uemf shadow-sm">SE CONNECTER <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center mb-4">
                    <div class="bg-light text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="fas fa-shield-alt fa-2x"></i></div>
                    <h5 class="fw-bold">Double Authentification</h5>
                    <p class="text-muted small mb-0">Entrez le code de votre application.</p>
                </div>
                <form method="POST">
                    <div class="mb-4 px-4">
                        <input type="text" name="otp_code" class="form-control form-control-lg text-center letter-spacing-2" maxlength="6" placeholder="000 000" autofocus required autocomplete="off">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="otp_verify_btn" class="btn btn-primary-uemf shadow-sm">VALIDER <i class="fas fa-check ms-2"></i></button>
                        <a href="login.php?logout=temp" class="btn btn-link text-muted btn-sm text-decoration-none">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light text-center py-3 border-top small text-muted">&copy; 2026 EIDIA - Plateforme de Gestion des Soutenances</div>
    </div>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-life-ring me-2 text-primary"></i>Besoin d'aide ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-4 text-muted">Si vous avez oublié votre mot de passe ou si votre compte est bloqué, vous devez ouvrir un ticket auprès du coordinateur.</p>
                    <div class="d-grid gap-2">
                        <a href="support.php" class="btn btn-outline-primary fw-bold py-2"><i class="fas fa-envelope-open-text me-2"></i>Contacter le Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>