<?php
session_start();
require_once '../../../config/database.php';
require_once '../../../src/Services/GoogleAuthenticator.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

$ga = new GoogleAuthenticator();
$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// 1. GESTION DU SECRET
// On vérifie si l'utilisateur a déjà un secret en base
$stmt = $pdo->prepare("SELECT otp_secret, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$secret = $user['otp_secret'];
$is_activated = !empty($secret);

// Si pas de secret, on en génère un temporaire (stocké en session pour l'instant)
if (!$secret) {
    if (!isset($_SESSION['temp_secret'])) {
        $_SESSION['temp_secret'] = $ga->createSecret();
    }
    $secret_to_show = $_SESSION['temp_secret'];
} else {
    $secret_to_show = $secret;
}

// 2. ACTIVATION
if (isset($_POST['activate_otp'])) {
    $code = $_POST['otp_code'];
    
    // On vérifie le code avec le secret temporaire
    if ($ga->verifyCode($secret_to_show, $code)) {
        // C'est bon ! On sauvegarde le secret définitivement en base
        $pdo->prepare("UPDATE users SET otp_secret = ? WHERE id = ?")->execute([$secret_to_show, $user_id]);
        $is_activated = true;
        $message = "Double authentification activée avec succès !";
        $msg_type = "success";
        unset($_SESSION['temp_secret']); // Plus besoin du temporaire
    } else {
        $message = "Code incorrect. Veuillez réessayer.";
        $msg_type = "danger";
    }
}

// 3. DÉSACTIVATION
if (isset($_POST['disable_otp'])) {
    $pdo->prepare("UPDATE users SET otp_secret = NULL WHERE id = ?")->execute([$user_id]);
    $is_activated = false;
    $secret = null;
    unset($_SESSION['temp_secret']);
    $message = "Double authentification désactivée.";
    $msg_type = "warning";
    
    // On régénère un nouveau secret pour l'affichage
    $_SESSION['temp_secret'] = $ga->createSecret();
    $secret_to_show = $_SESSION['temp_secret'];
}

// URL du QR Code
$qrCodeUrl = $ga->getQRCodeUrl('UEMF_PFE_' . $user['email'], $secret_to_show);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Sécurité OTP | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <a href="index.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left me-2"></i>Retour Dashboard</a>
        
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Double Authentification (2FA)</h5>
            </div>
            <div class="card-body p-4 text-center">
                
                <?php if($message): ?>
                    <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
                <?php endif; ?>

                <?php if($is_activated): ?>
                    <div class="text-success mb-4">
                        <i class="fas fa-shield-check fa-5x mb-3"></i>
                        <h3>Sécurité Activée</h3>
                        <p class="text-muted">Votre compte est protégé par Google Authenticator.</p>
                    </div>
                    <form method="POST">
                        <button type="submit" name="disable_otp" class="btn btn-outline-danger fw-bold" onclick="return confirm('Êtes-vous sûr de vouloir baisser votre niveau de sécurité ?');">
                            <i class="fas fa-trash me-2"></i>Désactiver la 2FA
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted mb-4">
                        Scannez ce QR Code avec l'application <strong>Google Authenticator</strong> ou <strong>Microsoft Authenticator</strong> sur votre téléphone.
                    </p>
                    
                    <div class="mb-4 border p-3 d-inline-block bg-white rounded">
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code" style="width: 200px; height: 200px;">
                    </div>
                    
                    <div class="mb-4">
                        <small class="text-muted">Code secret (si scan impossible) :</small><br>
                        <code class="fs-5 fw-bold text-dark"><?= $secret_to_show ?></code>
                    </div>

                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Entrez le code à 6 chiffres affiché sur l'app :</label>
                            <input type="text" name="otp_code" class="form-control form-control-lg text-center mx-auto" style="max-width: 200px; letter-spacing: 5px;" maxlength="6" required placeholder="000000">
                        </div>
                        <button type="submit" name="activate_otp" class="btn btn-primary px-5 fw-bold">
                            <i class="fas fa-lock me-2"></i>Activer la protection
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>