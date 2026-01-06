<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

// TRAITEMENTS
if (isset($_POST['valider_projet_id'])) {
    $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?")->execute([$_POST['valider_projet_id']]);
    $message = "Projet validé avec succès."; $msg_type = "success";
}
if (isset($_POST['supprimer_projet_id'])) {
    $pdo->prepare("DELETE FROM projets WHERE id = ?")->execute([$_POST['supprimer_projet_id']]);
    $message = "Projet supprimé."; $msg_type = "danger";
}

// REQUÊTE
$sql = "SELECT p.*, u.nom as nom_etudiant, u.prenom as prenom_etudiant 
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        ORDER BY FIELD(p.statut, 'inscrit') DESC, p.created_at DESC";
$projets = $pdo->query($sql)->fetchAll();

// STATS RAPIDES
$total = count($projets);
$en_attente = 0;
$valides = 0;
foreach($projets as $p) {
    if($p['statut'] == 'inscrit') $en_attente++;
    if($p['statut'] == 'valide_encadrant' || $p['statut'] == 'valide') $valides++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion Projets | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php"><i class="fas fa-university me-2"></i>UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="index.php" class="btn btn-outline-light btn-sm me-3 opacity-75"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                <span class="small text-uppercase me-3">Coordinateur</span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-list-ul me-2"></i>Répertoire des Projets</h2>
                    <p class="mb-0 opacity-75">Gérez l'ensemble des sujets déposés par les étudiants.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card-modern py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-modern-label">Total Dossiers</div>
                        <div class="stat-modern-value"><?= $total ?></div>
                    </div>
                    <i class="fas fa-folder text-primary fa-2x opacity-25"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-modern-label text-warning">En Attente</div>
                        <div class="stat-modern-value"><?= $en_attente ?></div>
                    </div>
                    <i class="fas fa-clock text-warning fa-2x opacity-25"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-modern-label text-success">Validés</div>
                        <div class="stat-modern-value"><?= $valides ?></div>
                    </div>
                    <i class="fas fa-check-circle text-success fa-2x opacity-25"></i>
                </div>
            </div>
        </div>

        <?php if(isset($message)): ?>
            <div class="alert alert-<?= $msg_type ?> border-0 shadow-sm mb-4"><i class="fas fa-info-circle me-2"></i><?= $message ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 bg-light" placeholder="Filtrer par nom ou sujet...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-light border"><i class="fas fa-filter me-1"></i> Filtres</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted text-uppercase small">
                            <tr>
                                <th class="ps-4">Étudiant</th>
                                <th>Sujet</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($projets as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 38px; height: 38px;">
                                                <?= substr($p['nom_etudiant'],0,1).substr($p['prenom_etudiant'],0,1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['nom_etudiant'].' '.$p['prenom_etudiant']) ?></div>
                                                <div class="small text-muted">M2 Big Data</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 350px;">
                                        <div class="fw-bold text-primary text-truncate"><?= htmlspecialchars($p['titre']) ?></div>
                                        <div class="small text-muted text-truncate"><?= htmlspecialchars($p['description']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($p['statut'] == 'inscrit'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>En attente</span>
                                        <?php elseif($p['statut'] == 'valide_encadrant'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Validé</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= $p['statut'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="details_projet.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Détails"><i class="fas fa-eye"></i></a>
                                            <?php if($p['statut'] == 'inscrit'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Valider ?');">
                                                    <input type="hidden" name="valider_projet_id" value="<?= $p['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                                <input type="hidden" name="supprimer_projet_id" value="<?= $p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
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