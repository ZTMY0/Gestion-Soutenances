<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Tableau de traduction des mois
$moisFr = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

$joursFr = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

// TRAITEMENT : Saisie de note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saisir_note'])) {
    $soutenance_id = intval($_POST['soutenance_id']);
    $note = floatval($_POST['note']);
    
    if ($note >= 0 && $note <= 20) {
        $stmt = $pdo->prepare("SELECT id FROM jurys WHERE soutenance_id = ? AND prof_id = ?");
        $stmt->execute([$soutenance_id, $prof_id]);
        
        if ($stmt->fetch()) {
            $stmtUpdate = $pdo->prepare("UPDATE soutenances SET note_finale = ? WHERE id = ?");
            if ($stmtUpdate->execute([$note, $soutenance_id])) {
                $stmtProj = $pdo->prepare("SELECT projet_id FROM soutenances WHERE id = ?");
                $stmtProj->execute([$soutenance_id]);
                $pid = $stmtProj->fetchColumn();
                if($pid) {
                    $pdo->prepare("UPDATE projets SET statut = 'soutenu' WHERE id = ?")->execute([$pid]);
                }
                $message = "Note enregistrée avec succès !";
                $messageType = "success";
            } else {
                $message = "Erreur lors de l'enregistrement de la note.";
                $messageType = "danger";
            }
        } else {
            $message = "Erreur : Vous n'êtes pas membre de ce jury.";
            $messageType = "danger";
        }
    } else {
        $message = "La note doit être entre 0 et 20.";
        $messageType = "danger";
    }
}

// Récupérer les jurys du prof
$sql = "SELECT s.id, s.date_soutenance, s.note_finale,
             sal.nom AS nom_salle,
             p.titre AS projet_titre,
             u.nom AS etudiant_nom,
             b.nom AS binome_nom,
             b.email AS binome_email,
             f.nom AS filiere_nom,
             j.role_jury AS mon_role
         FROM soutenances s
         JOIN jurys j ON j.soutenance_id = s.id
         JOIN projets p ON s.projet_id = p.id
         JOIN users u ON p.etudiant_id = u.id
         LEFT JOIN salles sal ON s.salle_id = sal.id
         LEFT JOIN users b ON p.binome_id = b.id
         LEFT JOIN filieres f ON p.filiere_id = f.id
         WHERE j.prof_id = ?
         ORDER BY s.date_soutenance ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$prof_id]);
$jurys = $stmt->fetchAll();

// Séparer jurys à venir et passés
$jurysAVenir = [];
$jurysPasses = [];
$now = date('Y-m-d H:i:s');

foreach ($jurys as $jury) {
    $dateSoutenance = $jury['date_soutenance'] ?? date('Y-m-d H:i:s');
    if ($dateSoutenance >= $now) {
        $jurysAVenir[] = $jury;
    } else {
        $jurysPasses[] = $jury;
    }
}

// Statistiques
$nbTotal = count($jurys);
$nbAVenir = count($jurysAVenir);
$nbNotes = 0;
foreach ($jurysPasses as $j) {
    if (isset($j['note_finale']) && $j['note_finale'] !== null) $nbNotes++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Jurys - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body>
    
    <!-- NAVBAR HARMONISÉE -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>UEMF Professeur
            </a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small">Pr. <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    <span class="d-none d-md-inline">Déconnexion</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO HARMONISÉ -->
    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-gavel me-2"></i>
                        Mes Participations aux Jurys
                    </h2>
                    <p class="mb-0 opacity-75">Consultez vos convocations et saisissez vos notes de soutenance</p>
                </div>
                <a href="index.php" class="btn btn-sm btn-outline-light d-none d-md-inline">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">

        <!-- MESSAGES -->
        <?php if ($message): ?>
            <div class="alert-modern <?= $messageType ?> animate-fade-in">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <div>
                    <strong><?= $messageType === 'success' ? 'Succès !' : 'Erreur !' ?></strong><br>
                    <span class="small"><?= $message ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div class="stat-number"><?= $nbTotal ?></div>
                    <div class="stat-label">Total Jurys</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-number"><?= $nbAVenir ?></div>
                    <div class="stat-label">À venir</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?= $nbNotes ?>/<?= count($jurysPasses) ?></div>
                    <div class="stat-label">Notes saisies</div>
                </div>
            </div>
        </div>

        <!-- JURYS À VENIR -->
        <?php if (!empty($jurysAVenir)): ?>
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-warning text-dark py-3 fw-bold">
                <i class="fas fa-calendar-check me-2"></i>
                Soutenances à venir (<?= count($jurysAVenir) ?>)
            </div>
            <div class="card-body p-3">
                <?php foreach ($jurysAVenir as $jury): 
                    $dateObj = new DateTime($jury['date_soutenance']);
                    $jourSemaine = $joursFr[$dateObj->format('w')];
                ?>
                <div class="jury-card mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <div class="jury-date-badge">
                                    <div class="jury-date-day"><?= $dateObj->format('d') ?></div>
                                    <div class="text-muted small fw-semibold"><?= $moisFr[$dateObj->format('n')] ?></div>
                                    <div class="text-primary small fw-bold mt-1"><?= $jourSemaine ?></div>
                                    <div class="text-muted small mt-2">
                                        <i class="far fa-clock me-1"></i>
                                        <?= $dateObj->format('H:i') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="mb-2 fw-bold text-primary">
                                    <i class="fas fa-project-diagram me-2"></i>
                                    <?= htmlspecialchars($jury['projet_titre']) ?>
                                </h6>
                                <p class="text-muted mb-2 small">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    <strong><?= htmlspecialchars($jury['etudiant_nom']) ?></strong>
                                    <?php if (!empty($jury['binome_nom'])): ?>
                                        <span class="ms-2">
                                            <i class="fas fa-users me-1"></i>
                                            & <?= htmlspecialchars($jury['binome_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge-modern secondary">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?= htmlspecialchars($jury['filiere_nom'] ?? 'N/A') ?>
                                    </span>
                                    <span class="badge-modern primary">
                                        <i class="fas fa-door-open me-1"></i>
                                        <?= htmlspecialchars($jury['nom_salle'] ?? 'Salle à définir') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <?php 
                                $roleIcon = '';
                                $roleText = ucfirst($jury['mon_role'] ?? 'Membre');
                                switch($jury['mon_role']) {
                                    case 'president': 
                                        $roleClass = 'role-president'; 
                                        $roleIcon = 'crown'; 
                                        $roleText = 'Président';
                                        break;
                                    case 'examinateur': 
                                        $roleClass = 'role-examinateur'; 
                                        $roleIcon = 'search'; 
                                        $roleText = 'Examinateur';
                                        break;
                                    case 'rapporteur': 
                                        $roleClass = 'role-rapporteur'; 
                                        $roleIcon = 'file-alt';
                                        $roleText = 'Rapporteur';
                                        break;
                                    default: 
                                        $roleClass = 'badge-modern secondary'; 
                                        $roleIcon = 'user';
                                }
                                ?>
                                <div class="role-badge <?= $roleClass ?> d-inline-flex">
                                    <i class="fas fa-<?= $roleIcon ?> me-1"></i>
                                    <?= $roleText ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- JURYS PASSÉS -->
        <?php if (!empty($jurysPasses)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white py-3 fw-bold">
                <i class="fas fa-history me-2"></i>
                Soutenances passées (<?= count($jurysPasses) ?>)
            </div>
            <div class="card-body p-3">
                <?php foreach ($jurysPasses as $jury): 
                    $aNote = ($jury['note_finale'] !== null);
                    $dateObj = new DateTime($jury['date_soutenance']);
                ?>
                <div class="jury-card passe mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-2 mb-md-0">
                                <div class="small fw-bold text-muted"><?= $dateObj->format('d/m/Y') ?></div>
                                <div class="small text-muted"><?= $dateObj->format('H:i') ?></div>
                            </div>
                            
                            <div class="col-md-4 mb-2 mb-md-0">
                                <h6 class="mb-1 fw-bold small"><?= htmlspecialchars($jury['projet_titre']) ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    <?= htmlspecialchars($jury['etudiant_nom']) ?>
                                </small>
                            </div>
                            
                            <div class="col-md-2 text-center mb-2 mb-md-0">
                                <span class="badge-modern secondary small">
                                    <?= ucfirst($jury['mon_role'] ?? 'Membre') ?>
                                </span>
                            </div>
                            
                            <div class="col-md-4">
                                <?php if ($aNote): ?>
                                    <div class="text-center">
                                        <div class="badge-modern success px-3 py-2">
                                            <i class="fas fa-star me-1"></i>
                                            <?= number_format($jury['note_finale'], 2) ?> / 20
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Note enregistrée
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="row g-2 align-items-center">
                                        <input type="hidden" name="soutenance_id" value="<?= $jury['id'] ?>">
                                        <div class="col-7">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">
                                                    <i class="fas fa-star text-warning"></i>
                                                </span>
                                                <input type="number" step="0.1" min="0" max="20" name="note" class="form-control" placeholder="Note /20" required>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <button type="submit" name="saisir_note" class="btn btn-success-modern btn-sm w-100">
                                                <i class="fas fa-check me-1"></i>
                                                Valider
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- AUCUN JURY -->
        <?php if (empty($jurys)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-gavel fa-4x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">Aucune participation aux jurys</h5>
                <p class="text-muted small">Vous n'avez pas encore été assigné à des jurys de soutenance</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>