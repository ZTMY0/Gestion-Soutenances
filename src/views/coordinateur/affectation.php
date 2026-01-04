<?php
session_start();
require_once '../../../config/database.php';
require_once '../../services/AffectationService.php'; 

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$service = new AffectationService($pdo);
$message = "";
$msg_type = "";

// 1. TRAITEMENT : VALIDATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider') {
    $resultat = $service->genererPropositionAffectation();
    
    if (!empty($resultat['affectations'])) {
        try {
            $rapport = $service->appliquerToutesAffectations($resultat['affectations']);
            $message = "<strong>Succès !</strong> " . $rapport['succes'] . " projets ont été affectés définitivement.";
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

// 2. SIMULATION
$simulation = $service->genererPropositionAffectation();
$stats = $simulation['stats'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Affectation IA | UEMF</title>
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
            </div>
        </div>
    </nav>

    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-magic me-2"></i>Affectation Intelligente</h2>
                    <p class="mb-0 opacity-75">Algorithme d'optimisation pour le matching Étudiants / Encadrants.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -3rem;">
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?> shadow-sm border-0 mb-4 rounded">
                <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card-modern p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="stat-modern-label text-primary">Projets à traiter</div>
                        <div class="stat-modern-value"><?php echo $stats['total_projets']; ?></div>
                    </div>
                    <i class="fas fa-layer-group stat-modern-icon text-primary"></i>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card-modern p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="stat-modern-label text-success">Propositions</div>
                        <div class="stat-modern-value text-success"><?php echo $stats['affectes']; ?></div>
                        <div class="small text-muted mt-1">Taux : <strong><?php echo $stats['taux_affectation']; ?>%</strong></div>
                    </div>
                    <i class="fas fa-check-circle stat-modern-icon text-success"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card-modern p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="stat-modern-label text-warning">Pertinence (IA)</div>
                        <div class="stat-modern-value text-warning"><?php echo $stats['score_moyen']; ?><small class="fs-6 text-muted">/100</small></div>
                    </div>
                    <i class="fas fa-star stat-modern-icon text-warning"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card-modern p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="stat-modern-label text-danger">Non Résolus</div>
                        <div class="stat-modern-value text-danger"><?php echo $stats['non_affectes']; ?></div>
                    </div>
                    <i class="fas fa-exclamation-triangle stat-modern-icon text-danger"></i>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="fas fa-list-ol me-2 text-primary"></i>Résultats de la simulation</span>
                
                <?php if ($stats['affectes'] > 0): ?>
                    <form method="POST" onsubmit="return confirm('Confirmez-vous l\'affectation définitive ? Cette action modifiera la base de données.');">
                        <input type="hidden" name="action" value="valider">
                        <button type="submit" class="btn btn-success fw-bold shadow-sm">
                            <i class="fas fa-check-double me-2"></i>Valider & Appliquer
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-light border" disabled>Aucune proposition</button>
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4 py-3">Projet / Étudiant</th>
                                <th>Encadrant Suggéré</th>
                                <th class="text-center">Score</th>
                                <th>Justification (Mots-clés)</th>
                                <th class="text-end pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($simulation['affectations']) && empty($simulation['non_affectes'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4 opacity-50">
                                            <i class="fas fa-clipboard-check fa-3x text-success mb-3"></i>
                                            <h5 class="fw-bold">Tout est à jour</h5>
                                            <p>Tous les étudiants ont déjà un encadrant assigné.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($simulation['affectations'] as $aff): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold border" style="width: 40px; height: 40px;">
                                                <?= substr($aff['etudiant_nom'], 0, 1) . substr($aff['etudiant_prenom'] ?? '', 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($aff['projet_titre']) ?></div>
                                                <small class="text-muted"><i class="fas fa-user-graduate me-1"></i><?= htmlspecialchars($aff['etudiant_nom']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center text-primary fw-bold">
                                            <i class="fas fa-user-tie me-2"></i>
                                            <?= htmlspecialchars($aff['professeur_nom']) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $score = $aff['score'];
                                            $color = $score > 75 ? 'success' : ($score > 50 ? 'warning' : 'secondary');
                                        ?>
                                        <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?> px-3 rounded-pill"><?= $score ?>%</span>
                                    </td>
                                    <td>
                                        <?php foreach (array_slice($aff['raisons'], 0, 3) as $raison): ?>
                                            <span class="badge bg-light text-secondary border me-1 fw-normal"><?= htmlspecialchars($raison) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="text-end pe-4"><span class="badge bg-info text-white">IA Proposed</span></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ($simulation['non_affectes'] as $echec): ?>
                                <tr class="table-danger">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">!</div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($echec['projet_titre']) ?></div>
                                                <small class="text-danger"><?= htmlspecialchars($echec['etudiant_nom']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td colspan="2" class="text-danger fst-italic">
                                        <i class="fas fa-times-circle me-1"></i> Aucun encadrant compatible
                                    </td>
                                    <td class="text-danger small"><?= htmlspecialchars($echec['raison']) ?></td>
                                    <td class="text-end pe-4"><span class="badge bg-danger">ÉCHEC</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>