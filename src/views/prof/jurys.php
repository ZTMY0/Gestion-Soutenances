<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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

// Tableau de traduction des mois (Pour remplacer strftime)
$moisFr = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// TRAITEMENT : Saisie de note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saisir_note'])) {
    $soutenance_id = intval($_POST['soutenance_id']);
    $note = floatval($_POST['note']);
    $commentaire = trim($_POST['commentaire'] ?? '');
    
    if ($note >= 0 && $note <= 20) {
        // Vérifier que ce prof fait partie du jury
        $stmt = $pdo->prepare("SELECT id FROM jurys WHERE soutenance_id = ? AND prof_id = ?");
        $stmt->execute([$soutenance_id, $prof_id]);
        
        if ($stmt->fetch()) {
            // Mise à jour de la note dans la table soutenances
            $stmtUpdate = $pdo->prepare("UPDATE soutenances SET note_finale = ? WHERE id = ?");
            if ($stmtUpdate->execute([$note, $soutenance_id])) {
                
                // Optionnel : Mettre à jour le projet en "Terminé" si la note est validée
                // Récupérer l'ID du projet
                $stmtProj = $pdo->prepare("SELECT projet_id FROM soutenances WHERE id = ?");
                $stmtProj->execute([$soutenance_id]);
                $pid = $stmtProj->fetchColumn();
                if($pid) {
                    $pdo->prepare("UPDATE projets SET statut = 'soutenu', note_finale = ? WHERE id = ?")->execute([$note, $pid]);
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
             p.titre AS projet_titre,
             p.description AS projet_description,
             u.nom AS etudiant_nom,
             p.binome_email AS binome_email,
             f.nom AS filiere_nom,
             s.salle AS salle_nom,
             j.role_jury AS mon_role
         FROM soutenances s
         JOIN jurys j ON j.soutenance_id = s.id
         JOIN projets p ON s.projet_id = p.id
         JOIN users u ON p.etudiant_id = u.id
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
    <title>Mes Jurys - Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .jury-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .jury-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .jury-card.passe {
            border-left-color: #6c757d;
            opacity: 0.9;
        }
        .jury-card.a-noter {
            border-left-color: #ffc107;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
        }
        .role-president { background: #dc3545; }
        .role-examinateur { background: #0d6efd; }
        .role-rapporteur { background: #198754; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 mb-4">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Espace Prof</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-chalkboard-teacher me-2"></i>Pr. <?php echo $_SESSION['user_nom']; ?></span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-gavel text-primary me-2"></i>Mes Participations aux Jurys</h2>
                <p class="text-muted mb-0">Consultez vos convocations et saisissez vos notes</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $nbTotal ?></h3>
                        <small>Total Jurys</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $nbAVenir ?></h3>
                        <small>À venir</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0"><?= $nbNotes ?>/<?= count($jurysPasses) ?></h3>
                        <small>Notes saisies</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($jurysAVenir)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Soutenances à venir (<?= count($jurysAVenir) ?>)</h5>
            </div>
            <div class="card-body">
                <?php foreach ($jurysAVenir as $jury): ?>
                <div class="card jury-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="bg-light rounded p-3">
                                    <div class="fs-4 fw-bold text-primary">
                                        <?= date('d', strtotime($jury['date_soutenance'])) ?>
                                    </div>
                                    
                                    <div class="text-muted small">
                                        <?= $moisFr[date('n', strtotime($jury['date_soutenance']))] ?>
                                    </div>
                                    
                                    <div class="text-muted small">
                                        <?= date('H:i', strtotime($jury['date_soutenance'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1"><?= htmlspecialchars($jury['projet_titre']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($jury['etudiant_nom']) ?>
                                    <?php if (!empty($jury['binome_email'])): ?>
                                        & <?= htmlspecialchars($jury['binome_email']) ?>
                                    <?php endif; ?>
                                </p>
                                <span class="badge bg-secondary me-2">
                                    <i class="fas fa-graduation-cap me-1"></i><?= htmlspecialchars($jury['filiere_nom'] ?? 'N/A') ?>
                                </span>
                                <span class="badge bg-info">
                                    <i class="fas fa-door-open me-1"></i><?= htmlspecialchars($jury['salle_nom'] ?? 'À définir') ?>
                                </span>
                            </div>
                            <div class="col-md-2 text-center">
                                <?php 
                                $roleClass = '';
                                $roleIcon = '';
                                switch($jury['mon_role']) {
                                    case 'president': $roleClass = 'role-president'; $roleIcon = 'crown'; break;
                                    case 'examinateur': $roleClass = 'role-examinateur'; $roleIcon = 'search'; break;
                                    case 'rapporteur': $roleClass = 'role-rapporteur'; $roleIcon = 'file-alt'; break;
                                    default: $roleClass = 'bg-secondary'; $roleIcon = 'user';
                                }
                                ?>
                                <span class="badge role-badge <?= $roleClass ?>">
                                    <i class="fas fa-<?= $roleIcon ?> me-1"></i>
                                    <?= ucfirst($jury['mon_role'] ?? 'Membre') ?>
                                </span>
                            </div>
                            
                            <div class="col-md-2 text-end">
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="soutenance_id" value="<?= $jury['id'] ?>">
                                    </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($jurysPasses)): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Soutenances passées (<?= count($jurysPasses) ?>)</h5>
            </div>
            <div class="card-body">
                <?php foreach ($jurysPasses as $jury): ?>
                <?php $aNote = ($jury['note_finale'] !== null); ?>
                <div class="card jury-card mb-3 passe">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="bg-light rounded p-2">
                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($jury['date_soutenance'])) ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?= htmlspecialchars($jury['projet_titre']) ?></h6>
                                <small class="text-muted">
                                    <?= htmlspecialchars($jury['etudiant_nom']) ?>
                                    <?php if (!empty($jury['binome_email'])): ?> & <?= htmlspecialchars($jury['binome_email']) ?><?php endif; ?>
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-secondary"><?= ucfirst($jury['mon_role'] ?? 'Membre') ?></span>
                            </div>
                            
                            <div class="col-md-4">
                                <?php if ($aNote): ?>
                                    <div class="text-center">
                                        <span class="badge bg-success fs-5 px-3 py-2"><?= number_format($jury['note_finale'], 2) ?> / 20</span>
                                        <div class="small text-muted mt-1"><i class="fas fa-check-circle"></i> Noté</div>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="soutenance_id" value="<?= $jury['id'] ?>">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text fw-bold">Note</span>
                                            <input type="number" step="0.1" min="0" max="20" name="note" class="form-control" placeholder="/20" required>
                                            <button type="submit" name="saisir_note" class="btn btn-primary fw-bold">OK</button>
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

        <?php if (empty($jurys)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
                <h5>Aucune participation aux jurys</h5>
                <p class="text-muted">Vous n'avez pas encore été assigné à des jurys de soutenance.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>