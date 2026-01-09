<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// ---------------------------------------------------------
// 1. TRAITEMENT : AFFECTATION AUTOMATIQUE (IA JURY)
// ---------------------------------------------------------
if (isset($_POST['auto_jury'])) {
    try {
        $pdo->beginTransaction();
        
        // A. Récupérer les soutenances SANS jury complet (moins de 2 membres)
        $sql = "SELECT s.id 
                FROM soutenances s 
                LEFT JOIN jurys j ON s.id = j.soutenance_id 
                GROUP BY s.id 
                HAVING COUNT(j.id) < 2";
        $soutenancesVides = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

        // B. Récupérer tous les professeurs
        $profs = $pdo->query("SELECT id FROM users WHERE role = 'prof'")->fetchAll(PDO::FETCH_COLUMN);

        if (count($profs) < 2) {
            throw new Exception("Pas assez de professeurs dans la base (minimum 2 requis).");
        }

        $count = 0;
        if (!empty($soutenancesVides)) {
            // Préparation des requêtes
            $stmtInsert = $pdo->prepare("INSERT INTO jurys (soutenance_id, prof_id, role_jury) VALUES (?, ?, ?)");
            $stmtClean  = $pdo->prepare("DELETE FROM jurys WHERE soutenance_id = ?");

            foreach ($soutenancesVides as $sid) {
                // 1. Nettoyage préventif (au cas où il y en aurait 1 seul)
                $stmtClean->execute([$sid]);

                // 2. Tirage au sort de 2 profs distincts
                $keys = array_rand($profs, 2);
                $p1 = $profs[$keys[0]];
                $p2 = $profs[$keys[1]];

                // 3. Insertion
                $stmtInsert->execute([$sid, $p1, 'president']);
                $stmtInsert->execute([$sid, $p2, 'examinateur']);
                
                $count++;
            }
        }
        
        $pdo->commit();
        
        if ($count > 0) {
            $message = "<strong>Succès IA !</strong> $count jury(s) ont été constitués automatiquement.";
            $msg_type = "success";
        } else {
            $message = "Tous les jurys sont déjà complets.";
            $msg_type = "warning";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Erreur IA : " . $e->getMessage();
        $msg_type = "danger";
    }
}

// ---------------------------------------------------------
// 2. TRAITEMENT : AFFECTATION MANUELLE (Classique)
// ---------------------------------------------------------
if (isset($_POST['affecter_jury'])) {
    $soutenance_id = $_POST['soutenance_id'];
    $president_id = $_POST['president_id'];
    $examinateur_id = $_POST['examinateur_id'];

    if ($president_id == $examinateur_id) {
        $message = "Le Président et l'Examinateur doivent être différents.";
        $msg_type = "danger";
    } else {
        try {
            $pdo->beginTransaction();

            // Reset du jury pour cette soutenance
            $stmt = $pdo->prepare("DELETE FROM jurys WHERE soutenance_id = ?");
            $stmt->execute([$soutenance_id]);

            // Insertion Président
            $stmt = $pdo->prepare("INSERT INTO jurys (soutenance_id, prof_id, role_jury) VALUES (?, ?, 'president')");
            $stmt->execute([$soutenance_id, $president_id]);

            // Insertion Examinateur
            $stmt = $pdo->prepare("INSERT INTO jurys (soutenance_id, prof_id, role_jury) VALUES (?, ?, 'examinateur')");
            $stmt->execute([$soutenance_id, $examinateur_id]);

            $pdo->commit();
            $message = "Jury modifié manuellement.";
            $msg_type = "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erreur technique : " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}

// ---------------------------------------------------------
// 3. RÉCUPÉRATION DES DONNÉES
// ---------------------------------------------------------
$sql = "SELECT s.id as sid, s.date_soutenance, s.salle,
               p.titre, u.nom as etu_nom, u.prenom as etu_prenom,
               j1.prof_id as pres_id, u1.nom as pres_nom, u1.prenom as pres_prenom,
               j2.prof_id as exam_id, u2.nom as exam_nom, u2.prenom as exam_prenom
        FROM soutenances s
        JOIN projets p ON s.projet_id = p.id
        JOIN users u ON p.etudiant_id = u.id
        LEFT JOIN jurys j1 ON s.id = j1.soutenance_id AND j1.role_jury = 'president'
        LEFT JOIN users u1 ON j1.prof_id = u1.id
        LEFT JOIN jurys j2 ON s.id = j2.soutenance_id AND j2.role_jury = 'examinateur'
        LEFT JOIN users u2 ON j2.prof_id = u2.id
        ORDER BY s.date_soutenance ASC";
$soutenances = $pdo->query($sql)->fetchAll();

// Liste des profs pour le select
$profs = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'prof' ORDER BY nom")->fetchAll();

// Stats
$total = count($soutenances);
$complets = 0;
foreach($soutenances as $s) { if($s['pres_id'] && $s['exam_id']) $complets++; }
$incomplets = $total - $complets;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Jurys | UEMF</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="/Gestion-Soutenances/public/assets/css/style.css">

    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com;">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="index.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                <span class="me-3 small text-uppercase">Coordinateur</span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-gavel me-2"></i>Gestion des Jurys</h2>
                    <p class="mb-0 opacity-75">Constitution des commissions d'évaluation.</p>
                </div>
                <form method="POST">
                    <button type="submit" name="auto_jury" class="btn btn-light text-primary fw-bold shadow-sm">
                        <i class="fas fa-magic me-2"></i>Constituer Jurys Auto
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -3rem;">
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card-modern p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-modern-label">Total Soutenances</div>
                        <div class="stat-modern-value"><?= $total ?></div>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x opacity-25 text-primary"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-modern-label text-success">Jurys Complets</div>
                        <div class="stat-modern-value"><?= $complets ?></div>
                    </div>
                    <i class="fas fa-users fa-2x opacity-25 text-success"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-modern-label text-danger">À Assigner</div>
                        <div class="stat-modern-value"><?= $incomplets ?></div>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-25 text-danger"></i>
                </div>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $msg_type ?> shadow-sm border-0 mb-4 rounded d-flex align-items-center fade show">
                <i class="fas fa-<?= ($msg_type=='success')?'check-circle':'exclamation-triangle' ?> me-3 fa-lg"></i>
                <div><?= $message ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Liste des sessions</h6>
            </div>
            <div class="card-body p-0">
                
                <?php if(empty($soutenances)): ?>
                    <div class="text-center py-5 opacity-50">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>Aucune soutenance planifiée pour le moment.</p>
                        <a href="planification.php" class="btn btn-primary btn-sm">Aller au planning</a>
                    </div>
                <?php else: ?>
                    
                    <div class="accordion accordion-flush" id="accordionJurys">
                        <?php foreach($soutenances as $index => $s): ?>
                            <?php 
                                $is_complete = ($s['pres_id'] && $s['exam_id']);
                                $status_color = $is_complete ? 'success' : 'warning';
                                $status_text = $is_complete ? 'Complet' : 'Incomplet';
                            ?>
                            <div class="accordion-item border-bottom">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= ($index!==0)?'collapsed':'' ?> bg-white text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $s['sid'] ?>">
                                        <div class="row w-100 align-items-center">
                                            <div class="col-md-3">
                                                <span class="fw-bold text-primary"><?= date('d/m/Y H:i', strtotime($s['date_soutenance'])) ?></span>
                                                <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($s['salle']) ?></div>
                                            </div>
                                            <div class="col-md-4">
                                                <strong><?= htmlspecialchars($s['etu_nom'].' '.$s['etu_prenom']) ?></strong>
                                                <div class="small text-muted text-truncate" style="max-width: 250px;"><?= htmlspecialchars($s['titre']) ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <?php if($is_complete): ?>
                                                    <div class="small"><span class="badge bg-light text-dark border">P</span> <?= $s['pres_nom'] ?></div>
                                                    <div class="small"><span class="badge bg-light text-dark border">E</span> <?= $s['exam_nom'] ?></div>
                                                <?php else: ?>
                                                    <span class="small text-muted fst-italic">-- En attente --</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2 text-end pe-4">
                                                <span class="badge bg-<?= $status_color ?>"><?= $status_text ?></span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $s['sid'] ?>" class="accordion-collapse collapse <?= ($index===0)?'show':'' ?>" data-bs-parent="#accordionJurys">
                                    <div class="accordion-body bg-light p-4">
                                        
                                        <form method="POST" class="row g-3 align-items-end">
                                            <input type="hidden" name="soutenance_id" value="<?= $s['sid'] ?>">
                                            
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Président du Jury</label>
                                                <select name="president_id" class="form-select" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php foreach($profs as $p): ?>
                                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $s['pres_id'])?'selected':'' ?>>
                                                            <?= $p['nom'].' '.$p['prenom'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Examinateur</label>
                                                <select name="examinateur_id" class="form-select" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php foreach($profs as $p): ?>
                                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $s['exam_id'])?'selected':'' ?>>
                                                            <?= $p['nom'].' '.$p['prenom'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <button name="affecter_jury" type="submit" class="btn btn-primary w-100 fw-bold">
                                                    <i class="fas fa-save me-1"></i> Sauver
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>