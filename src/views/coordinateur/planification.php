<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// 2. TRAITEMENT : ENREGISTRER UNE SOUTENANCE
if (isset($_POST['planifier_btn'])) {
    $projet_id = $_POST['projet_id'];
    $date = $_POST['date_soutenance'];
    $salle = $_POST['salle'];
    
    // Vérifier doublon
    $check = $pdo->prepare("SELECT id FROM soutenances WHERE projet_id = ?");
    $check->execute([$projet_id]);
    
    if ($check->rowCount() > 0) {
        $message = "Ce projet a déjà une soutenance programmée.";
        $msg_type = "warning";
    } else {
        // Insertion
        $sql = "INSERT INTO soutenances (projet_id, date_soutenance, salle) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$projet_id, $date, $salle])) {
            
            // On force le statut 'valide' pour assurer l'affichage vert chez l'étudiant
            $pdo->prepare("UPDATE projets SET statut = 'valide' WHERE id = ?")->execute([$projet_id]);
            
            $message = "Soutenance programmée avec succès.";
            $msg_type = "success";
        } else {
            $message = "Erreur lors de l'enregistrement.";
            $msg_type = "danger";
        }
    }
}

// 3. RÉCUPÉRATION DES PROJETS PRÊTS À SOUTENIR
$sql = "SELECT p.*, u.nom, u.prenom 
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        WHERE (p.statut = 'valide' 
               OR p.statut = 'pret_soutenance' 
               OR (p.statut = 'valide_encadrant' AND p.rapport_chemin IS NOT NULL AND p.rapport_chemin != ''))
        AND p.id NOT IN (SELECT projet_id FROM soutenances)";

$projets_a_planifier = $pdo->query($sql)->fetchAll();

// 4. LISTE DES SOUTENANCES EXISTANTES
$sqlSoutenances = "SELECT s.*, p.titre, u.nom, u.prenom 
                   FROM soutenances s
                   JOIN projets p ON s.projet_id = p.id
                   JOIN users u ON p.etudiant_id = u.id
                   ORDER BY s.date_soutenance ASC";
$liste_soutenances = $pdo->query($sqlSoutenances)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Planification PFE | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-uemf sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-university me-2"></i>UEMF GESTION PFE</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small text-uppercase fw-bold">Coordinateur</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>Planification des Soutenances</h4>
                <p class="text-muted small mb-0">Gestion des dates, horaires et salles d'examen.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-2"></i>Retour Tableau de bord
            </a>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?> shadow-sm border-0 d-flex align-items-center mb-4">
                <i class="fas fa-<?php echo ($msg_type == 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2 fa-lg"></i>
                <div><?php echo $message; ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <div class="col-lg-5">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark"><i class="fas fa-clock me-2 text-warning"></i>À Planifier</span>
                        <span class="badge bg-warning text-dark border"><?php echo count($projets_a_planifier); ?> en attente</span>
                    </div>
                    
                    <div class="card-body p-0 bg-light">
                        <?php if (empty($projets_a_planifier)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i><br>
                                <h6 class="fw-bold text-dark">Tout est à jour !</h6>
                                <p class="small">Aucun projet en attente de planification.</p>
                            </div>
                        <?php else: ?>
                            <div class="accordion accordion-flush" id="accordionPlanification">
                                <?php foreach ($projets_a_planifier as $index => $p): ?>
                                    <div class="accordion-item border-0 mb-3 bg-white shadow-sm mx-3 mt-3 rounded">
                                        <h2 class="accordion-header" id="heading<?php echo $p['id']; ?>">
                                            <button class="accordion-button <?php echo ($index !== 0) ? 'collapsed' : ''; ?> fw-bold text-dark bg-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $p['id']; ?>">
                                                <div class="d-flex align-items-center w-100">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                        <?php echo substr($p['nom'], 0, 1) . substr($p['prenom'], 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <div class="small text-uppercase text-muted" style="font-size: 0.7rem;">Étudiant</div>
                                                        <div><?php echo htmlspecialchars($p['nom'] . ' ' . $p['prenom']); ?></div>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $p['id']; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" data-bs-parent="#accordionPlanification">
                                            <div class="accordion-body pt-0 pb-3 px-3">
                                                <div class="small text-muted mb-3 border-bottom pb-2">
                                                    <strong>Sujet :</strong> <?php echo htmlspecialchars($p['titre']); ?><br>
                                                    <a href="../../../public/uploads/<?= $p['rapport_chemin'] ?>" target="_blank" class="text-decoration-none mt-1 d-inline-block"><i class="fas fa-file-pdf me-1"></i>Voir le rapport</a>
                                                </div>

                                                <form method="POST">
                                                    <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                                    
                                                    <div class="mb-2">
                                                        <label class="form-label small fw-bold text-secondary mb-1">Date & Heure</label>
                                                        <input type="datetime-local" name="date_soutenance" class="form-control form-control-sm" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-secondary mb-1">Lieu</label>
                                                        <select name="salle" class="form-select form-select-sm" required>
                                                            <option value="">Sélectionner une salle...</option>
                                                            <optgroup label="Amphithéâtres">
                                                                <option value="Grand Amphi">Grand Amphithéâtre</option>
                                                                <option value="Amphi A">Amphi A</option>
                                                                <option value="Amphi B">Amphi B</option>
                                                            </optgroup>
                                                            <optgroup label="Bâtiment 1 (Génie Info)">
                                                                <option value="B1 - 0.05">B1 - 0.05 (RDC)</option>
                                                                <option value="B1 - 1.05">B1 - 1.05 (1er étage)</option>
                                                                <option value="B1 - 2.04">B1 - 2.04</option>
                                                            </optgroup>
                                                            <optgroup label="Distanciel">
                                                                <option value="Visio (Teams)">Visio (Teams)</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>

                                                    <button type="submit" name="planifier_btn" class="btn btn-success btn-sm w-100 fw-bold">
                                                        <i class="fas fa-check me-2"></i>Valider la date
                                                    </button>
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

            <div class="col-lg-7">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark"><i class="fas fa-calendar-check me-2 text-primary"></i>Planning Officiel</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Imprimer</button>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-uppercase text-secondary small" style="width: 20%;">Date</th>
                                        <th class="text-uppercase text-secondary small" style="width: 25%;">Étudiant</th>
                                        <th class="text-uppercase text-secondary small" style="width: 30%;">Sujet</th>
                                        <th class="text-uppercase text-secondary small" style="width: 15%;">Salle</th>
                                        <th class="text-end pe-4 text-uppercase text-secondary small">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($liste_soutenances)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                Aucune soutenance planifiée pour le moment.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($liste_soutenances as $s): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-primary"><?= date('d/m/Y', strtotime($s['date_soutenance'])) ?></div>
                                                    <div class="small text-muted"><?= date('H:i', strtotime($s['date_soutenance'])) ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="small text-muted text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($s['titre']) ?>">
                                                        <?= htmlspecialchars($s['titre']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($s['salle']) ?></span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="jurys.php?soutenance_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-dark" title="Gérer le Jury">
                                                        <i class="fas fa-gavel"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>