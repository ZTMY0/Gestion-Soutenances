<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// 1. TRAITEMENT : RÉINITIALISATION DU MOT DE PASSE PROF
if (isset($_POST['reset_prof_id'])) {
    $id_prof = $_POST['reset_prof_id'];
    
    // Mot de passe par défaut : "12345678"
    $default_password = password_hash("12345678", PASSWORD_DEFAULT);
    
    // On réinitialise aussi le secret OTP (2FA) au cas où il a perdu son téléphone
    $stmt = $pdo->prepare("UPDATE users SET password = ?, otp_secret = NULL WHERE id = ? AND role = 'prof'");
    
    if ($stmt->execute([$default_password, $id_prof])) {
        $message = "Accès réinitialisé pour ce professeur (MDP: 12345678 + 2FA désactivée).";
        $msg_type = "success";
    } else {
        $message = "Erreur technique.";
        $msg_type = "danger";
    }
}

// 2. LISTE DES PROFESSEURS
$sql = "SELECT u.id, u.nom, u.prenom, u.email, u.otp_secret 
        FROM users u 
        WHERE u.role = 'prof' 
        ORDER BY u.nom ASC";
$profs = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion Professeurs | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="index.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                <span class="me-3 small text-uppercase">Coordinateur</span>
            </div>
        </div>
    </nav>

    <div class="container pb-5 mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Gestion des Professeurs</h4>
            <div class="alert alert-warning py-1 px-3 mb-0 small">
                <i class="fas fa-exclamation-triangle me-1"></i> Le Reset remet le MDP à <strong>12345678</strong> et désactive la 2FA.
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $msg_type ?> shadow-sm border-0 d-flex align-items-center mb-4">
                <i class="fas fa-<?= ($msg_type == 'success') ? 'check-circle' : 'exclamation-circle' ?> me-3 fa-lg"></i>
                <div><?= $message ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Enseignant</th>
                                <th>Email</th>
                                <th>État Sécurité</th>
                                <th class="text-end pe-4">Dépannage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($profs as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($p['email']) ?></td>
                                    <td>
                                        <?php if(!empty($p['otp_secret'])): ?>
                                            <span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i>2FA Activée</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary opacity-50"><i class="fas fa-unlock me-1"></i>Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" onsubmit="return confirm('Attention : Le mot de passe de ce professeur sera 12345678. Confirmer ?');">
                                            <input type="hidden" name="reset_prof_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">
                                                <i class="fas fa-tools me-1"></i> Reset Accès
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>