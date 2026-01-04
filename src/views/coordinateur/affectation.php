<?php
session_start();
require_once '../../../config/database.php';
require_once '../../services/AffectationService.php';

// Sécurité : Seul le coordinateur peut accéder
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

$service = new AffectationService($pdo);
$message = "";
$msg_type = "";

// 1. Action : Appliquer les affectations (Enregistrement en BDD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider') {
    // On relance l'algo pour être sûr d'avoir les dernières données
    $resultat = $service->genererPropositionAffectation();
    
    if (!empty($resultat['affectations'])) {
        try {
            $rapport = $service->appliquerToutesAffectations($resultat['affectations']);
            $message = "Succès ! " . $rapport['succes'] . " projets affectés. (" . $rapport['echecs'] . " échecs)";
            $msg_type = "success";
        } catch (Exception $e) {
            $message = "Erreur technique : " . $e->getMessage();
            $msg_type = "danger";
        }
    } else {
        $message = "Aucune affectation à appliquer.";
        $msg_type = "warning";
    }
}

// 2. Action : Simulation (Défaut)
$simulation = $service->genererPropositionAffectation();
$stats = $simulation['stats'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Affectation Intelligente - Coordinateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../layout/navbar_coordinateur.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4 text-primary"><i class="fas fa-magic me-2"></i>Affectation Automatique des Encadrants</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-white border-start border-4 border-primary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Projets à traiter</h6>
                        <h2 class="mb-0 text-primary"><?php echo $stats['total_projets']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-start border-4 border-success shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Propositions trouvées</h6>
                        <h2 class="mb-0 text-success"><?php echo $stats['affectes']; ?></h2>
                        <small class="text-muted">Taux: <?php echo $stats['taux_affectation']; ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-start border-4 border-warning shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Score Moyen (Matching)</h6>
                        <h2 class="mb-0 text-warning"><?php echo $stats['score_moyen']; ?>/100</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-start border-4 border-danger shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small">Non Résolus</h6>
                        <h2 class="mb-0 text-danger"><?php echo $stats['non_affectes']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">
                    <i class="fas fa-list-ul me-2"></i>Proposition de l'Algorithme
                </h5>
                <?php if ($stats['affectes'] > 0): ?>
                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir valider ces affectations ? Cette action modifiera la base de données.');">
                        <input type="hidden" name="action" value="valider">
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-check-circle me-2"></i>VALIDER ET APPLIQUER
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Projet / Étudiant</th>
                                <th>Encadrant Proposé</th>
                                <th>Score</th>
                                <th>Justification (IA)</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($simulation['affectations']) && empty($simulation['non_affectes'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                                        Tous les projets ont déjà un encadrant !
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($simulation['affectations'] as $aff): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($aff['projet_titre']); ?></div>
                                        <small class="text-muted"><i class="fas fa-user-graduate me-1"></i><?php echo htmlspecialchars($aff['etudiant_nom']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($aff['professeur_nom']); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                            $badgeClass = $aff['score'] > 70 ? 'bg-success' : ($aff['score'] > 40 ? 'bg-warning text-dark' : 'bg-secondary');
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill">
                                            <?php echo $aff['score']; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <ul class="mb-0 small text-muted ps-3">
                                            <?php foreach ($aff['raisons'] as $raison): ?>
                                                <li><?php echo htmlspecialchars($raison); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td><span class="badge bg-info text-dark">PROPOSÉ</span></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ($simulation['non_affectes'] as $echec): ?>
                                <tr class="table-danger">
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo htmlspecialchars($echec['projet_titre']); ?></div>
                                        <small><i class="fas fa-user-graduate me-1"></i><?php echo htmlspecialchars($echec['etudiant_nom']); ?></small>
                                    </td>
                                    <td colspan="2" class="text-danger fst-italic">Aucun encadrant compatible</td>
                                    <td class="text-danger small"><?php echo htmlspecialchars($echec['raison']); ?></td>
                                    <td><span class="badge bg-danger">NON RÉSOLU</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-end">
             <a href="projets.php" class="btn btn-outline-secondary">Retour à la liste manuelle</a>
        </div>
    </div>
</body>
</html>