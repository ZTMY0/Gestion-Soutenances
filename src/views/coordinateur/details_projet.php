<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') { header("Location: ../auth/login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: projets.php"); exit(); }

$id = $_GET['id'];
$msg = "";

// ACTIONS
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'affecter') {
        $pdo->prepare("UPDATE projets SET encadrant_id = ? WHERE id = ?")->execute([$_POST['encadrant_id'], $id]);
        $msg = "Encadrant affecté.";
    } elseif ($_POST['action'] == 'valider') {
        $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?")->execute([$id]);
        $msg = "Projet validé.";
    }
}

// DATA
$projet = $pdo->query("SELECT p.*, u.nom as enom, u.prenom as eprenom, u.email as eemail, prof.id as pid, prof.nom as pnom, prof.prenom as pprenom FROM projets p JOIN users u ON p.etudiant_id = u.id LEFT JOIN users prof ON p.encadrant_id = prof.id WHERE p.id = $id")->fetch();
$profs = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'prof' ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Détail Projet | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="projets.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left me-1"></i> Retour liste</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero pb-5">
        <div class="container">
            <span class="badge bg-warning text-dark mb-2"><i class="fas fa-hashtag me-1"></i>Projet #<?= $projet['id'] ?></span>
            <h2 class="mb-1"><?= htmlspecialchars($projet['titre']) ?></h2>
            <p class="opacity-75"><i class="fas fa-user-graduate me-2"></i>Porté par <?= htmlspecialchars($projet['enom'].' '.$projet['eprenom']) ?></p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -3rem;">
        <?php if($msg): ?><div class="alert alert-success shadow-sm mb-4"><i class="fas fa-check me-2"></i><?= $msg ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <h5 class="text-primary fw-bold mb-3">Description du sujet</h5>
                        <p class="text-secondary lh-lg"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                        
                        <hr class="my-4 opacity-25">
                        
                        <h6 class="fw-bold mb-3">Stack Technique</h6>
                        <?php foreach(explode(',', $projet['technologies']) as $t): ?>
                            <span class="badge bg-light text-dark border me-1 px-3 py-2"><?= trim($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Encadrement</div>
                    <div class="card-body">
                        <?php if($projet['pid']): ?>
                            <div class="alert alert-success d-flex align-items-center border-0 mb-3">
                                <div class="bg-white text-success rounded-circle p-2 me-3"><i class="fas fa-check"></i></div>
                                <div><small class="text-uppercase fw-bold opacity-75">Assigné à</small><br><strong>Pr. <?= $projet['pnom'] ?></strong></div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Non assigné</div>
                        <?php endif; ?>

                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="affecter">
                            <label class="small fw-bold text-muted mb-1">Changer l'encadrant</label>
                            <div class="input-group mb-3">
                                <select name="encadrant_id" class="form-select">
                                    <option value="">Sélectionner...</option>
                                    <?php foreach($profs as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($p['id']==$projet['pid'])?'selected':'' ?>><?= $p['nom'].' '.$p['prenom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary"><i class="fas fa-save"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if($projet['statut'] == 'inscrit'): ?>
                    <div class="card shadow-sm border-0 bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="fw-bold">Validation Administrative</h6>
                            <p class="small opacity-75 mb-3">Ce sujet est en attente de validation.</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="valider">
                                <button class="btn btn-light text-success fw-bold w-100 shadow-sm">VALIDER LE DOSSIER</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>