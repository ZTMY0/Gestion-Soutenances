<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// 1. TRAITEMENT : RÉINITIALISATION DU MOT DE PASSE (ET DU 2FA)
if (isset($_POST['reset_etudiant_id'])) {
    $id_etudiant = $_POST['reset_etudiant_id'];
    
    // Mot de passe par défaut : "12345678"
    $default_password = password_hash("12345678", PASSWORD_DEFAULT);
    
    // IMPORTANT : On remet le mot de passe À ZÉRO et on DÉSACTIVE la 2FA (otp_secret = NULL)
    // Cela permet à l'étudiant de se reconnecter s'il a perdu son téléphone.
    $stmt = $pdo->prepare("UPDATE users SET password = ?, otp_secret = NULL WHERE id = ? AND role = 'etudiant'");
    
    if ($stmt->execute([$default_password, $id_etudiant])) {
        $message = "Compte réinitialisé avec succès (MDP: 12345678 + 2FA désactivée).";
        $msg_type = "success";
    } else {
        $message = "Erreur technique lors de la réinitialisation.";
        $msg_type = "danger";
    }
}

// 2. RÉCUPÉRATION DES ÉTUDIANTS AVEC LEUR PROJET
$sql = "SELECT u.*, p.titre as projet_titre, p.statut as projet_statut 
        FROM users u 
        LEFT JOIN projets p ON p.etudiant_id = u.id 
        WHERE u.role = 'etudiant' 
        ORDER BY u.nom ASC";
$etudiants = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion Étudiants | UEMF</title>
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
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-user-graduate me-2 text-primary"></i>Gestion des Étudiants</h4>
                <p class="text-muted small mb-0">Annuaire et dépannage des accès.</p>
            </div>
            
            <div class="alert alert-info py-2 px-3 mb-0 small border-start border-4 border-info">
                <i class="fas fa-info-circle me-1"></i> Le bouton <strong>Reset</strong> remet le mot de passe à <code>12345678</code> et supprime la sécurité 2FA.
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
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4">Étudiant</th>
                                <th>Email Académique</th>
                                <th>Situation PFE</th>
                                <th>Sécurité</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($etudiants as $e): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></div>
                                        <div class="small text-muted">ID: <?= $e['id'] ?></div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($e['email']) ?>" class="text-decoration-none text-secondary">
                                            <?= htmlspecialchars($e['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if($e['projet_titre']): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-light text-primary border text-truncate mb-1" style="max-width: 200px;">
                                                    <?= htmlspecialchars($e['projet_titre']) ?>
                                                </span>
                                                <?php 
                                                    $badgeClass = 'secondary';
                                                    if($e['projet_statut'] == 'valide') $badgeClass = 'success';
                                                    elseif($e['projet_statut'] == 'inscrit') $badgeClass = 'warning';
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 text-<?= $badgeClass ?> border border-<?= $badgeClass ?>" style="width: fit-content;">
                                                    <?= htmlspecialchars($e['projet_statut']) ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Sans sujet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($e['otp_secret'])): ?>
                                            <span class="badge bg-success rounded-pill"><i class="fas fa-shield-alt me-1"></i>2FA Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border rounded-pill">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" onsubmit="return confirm('⚠️ Êtes-vous sûr ?\n\nCela va :\n1. Remettre le mot de passe à 12345678\n2. Désactiver la double authentification\n\nContinuer ?');">
                                            <input type="hidden" name="reset_etudiant_id" value="<?= $e['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" title="Débloquer le compte">
                                                <i class="fas fa-unlock-alt me-1"></i> Reset Accès
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