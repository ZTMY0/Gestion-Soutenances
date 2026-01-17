<?php
// ---------------------------------------------------------
// CONFIGURATION & SÉCURITÉ
// ---------------------------------------------------------
ini_set('display_errors', 0); // En prod
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

// Inclusion BDD
require_once __DIR__ . '/../../../config/database.php';

// Vérification Rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// ---------------------------------------------------------
// RÉCUPÉRATION DES SALLES DE LA BDD
// ---------------------------------------------------------
$salles_disponibles = [];
try {
    $stmt_salles = $pdo->query("SELECT id, nom FROM salles ORDER BY nom");
    $salles_disponibles = $stmt_salles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur, par exemple, logguer et utiliser une liste vide
    error_log("Erreur de récupération des salles: " . $e->getMessage());
}

// ---------------------------------------------------------
// TRAITEMENT 1 : AUTO-PLANIFICATION (Algorithme)
// ---------------------------------------------------------
if (isset($_POST['run_auto_planning'])) {
    if (!empty($_POST['session_start'])) {
        try {
            $dateDebut = $_POST['session_start'];
            $plageJours = 15; 
            
            // Créneaux horaires standards
            $creneauxHoraires = ['08:30:00', '10:15:00', '13:30:00', '15:15:00', '17:00:00'];
            // $sallesDispo will be fetched from the service directly if used, or dynamically from DB for manual fallback

            $planningData = [];
            
            // 1. Essai via le Service (si disponible)
            $servicePath = __DIR__ . '/../../../src/Services/PlanificationService.php';
            if (file_exists($servicePath)) {
                require_once $servicePath;
                if (class_exists('PlanificationService')) {
                    try {
                        $service = new PlanificationService($pdo);
                        $dateFin = !empty($_POST['session_end']) ? $_POST['session_end'] : date('Y-m-d', strtotime($dateDebut . ' + 30 days'));
                        $resultat = $service->genererPlanningAutomatique($dateDebut, $dateFin);
                        $planningData = isset($resultat['planning']) ? $resultat['planning'] : (is_array($resultat) ? $resultat : []);
                    } catch (Exception $e) { 
                        $planningData = []; 
                    }
                }
            }

            // 2. Fallback : Algorithme manuel de secours
            if (empty($planningData)) {
                $sqlRecup = "SELECT p.id FROM projets p 
                             WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR p.statut = 'valide_encadrant')
                             AND p.id NOT IN (SELECT projet_id FROM soutenances)";
                
                try {
                    $stmt = $pdo->query($sqlRecup);
                    $projetsManuels = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
                } catch (Exception $e) { $projetsManuels = []; }

                if (!empty($projetsManuels)) {
                    $slotsOccupes = [];
                    // Fetch salles from DB for fallback also
                    $fallback_salles_data = $salles_disponibles; // Use the full array for IDs and names
                    if (empty($fallback_salles_data)) {
                        error_log("Aucune salle trouvée dans la BDD pour le fallback de planification.");
                        // Handle case with no salles, maybe skip auto-planning entirely or use a default
                    }

                    foreach ($projetsManuels as $pid) {
                        $creneauTrouve = false; $tentatives = 0;
                        while (!$creneauTrouve && $tentatives < 100) {
                            $tentatives++;
                            $randDay = rand(0, $plageJours);
                            $testDate = date('Y-m-d', strtotime($dateDebut . " + $randDay days"));
                            
                            // Pas de Samedi (6) ni Dimanche (7)
                            if (date('N', strtotime($testDate)) >= 6) continue; 

                            $testHeure = $creneauxHoraires[array_rand($creneauxHoraires)];
                            // Use a random salle ID from DB fetched list
                            $selected_fallback_salle = !empty($fallback_salles_data) ? $fallback_salles_data[array_rand($fallback_salles_data)] : ['id' => null, 'nom' => 'Salle par défaut'];
                            $testSalleId = $selected_fallback_salle['id'];
                            $testSalleNom = $selected_fallback_salle['nom'];
                            $cleUnique = $testDate . '_' . $testHeure . '_' . $testSalleNom;


                            if ($testSalleId !== null && !isset($slotsOccupes[$cleUnique])) {
                                $slotsOccupes[$cleUnique] = true;
                                $planningData[] = ['projet_id' => $pid, 'date_soutenance' => $testDate . ' ' . $testHeure, 'salle_id' => $testSalleId]; // Changed 'salle' to 'salle_id'
                                $creneauTrouve = true;
                            }
                        }
                    }
                }
            }

            // Insertion en base de données
            $count = 0;
            if (!empty($planningData)) {
                $stmtInsert = $pdo->prepare("INSERT INTO soutenances (projet_id, date_soutenance, salle_id) VALUES (?, ?, ?)"); // Changed 'salle' to 'salle_id'
                $stmtUpdate = $pdo->prepare("UPDATE projets SET statut = 'valide' WHERE id = ?");
                $checkStmt = $pdo->prepare("SELECT id FROM soutenances WHERE projet_id = ?");

                foreach ($planningData as $creneau) {
                    $checkStmt->execute([$creneau['projet_id']]);
                    if ($checkStmt->rowCount() == 0) {
                        $stmtInsert->execute([$creneau['projet_id'], $creneau['date_soutenance'], $creneau['salle_id']]); // Changed 'salle' to 'salle_id'
                        $stmtUpdate->execute([$creneau['projet_id']]);
                        $count++;
                    }
                }
            }
            
            if ($count > 0) { $message = "<strong>Succès !</strong> $count soutenance(s) planifiée(s)."; $msg_type = "success"; }
            else { $message = "Aucun nouveau créneau assigné (vérifiez s'il reste des projets à planifier)."; $msg_type = "warning"; }

        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage(); $msg_type = "danger";
        }
    }
}

// ---------------------------------------------------------
// TRAITEMENT 2 : PLANIFICATION MANUELLE
// ---------------------------------------------------------
if (isset($_POST['planifier_btn'])) {
    try {
        $projet_id = $_POST['projet_id'];
        $date = $_POST['date_soutenance'];
        $salle_id = (int)$_POST['salle']; // Changed variable name and cast to int
        
        $check = $pdo->prepare("SELECT id FROM soutenances WHERE projet_id = ?");
        $check->execute([$projet_id]);
        
        if ($check->rowCount() > 0) { $message = "Ce projet est déjà planifié."; $msg_type = "warning"; }
        else {
            $pdo->prepare("INSERT INTO soutenances (projet_id, date_soutenance, salle_id) VALUES (?, ?, ?)")->execute([$projet_id, $date, $salle_id]); // Changed 'salle' to 'salle_id' and used $salle_id
            $pdo->prepare("UPDATE projets SET statut = 'valide' WHERE id = ?")->execute([$projet_id]);
            $message = "Soutenance enregistrée avec succès."; $msg_type = "success";
        }
    } catch (PDOException $e) { $message = "Erreur SQL : " . $e->getMessage(); $msg_type = "danger"; }
}

// ---------------------------------------------------------
// CHARGEMENT DES DONNÉES
// ---------------------------------------------------------
$projets_a_planifier = [];
$liste_soutenances = [];

try {
    $sql = "SELECT p.*, u.nom, u.prenom 
            FROM projets p 
            JOIN users u ON p.etudiant_id = u.id 
            WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR p.statut = 'valide_encadrant')
            AND p.id NOT IN (SELECT projet_id FROM soutenances)";
    $projets_a_planifier = $pdo->query($sql)->fetchAll();

    $liste_soutenances = $pdo->query("SELECT s.id, s.date_soutenance, p.titre, u.nom, u.prenom, sal.nom as salle_nom FROM soutenances s JOIN projets p ON s.projet_id = p.id JOIN users u ON p.etudiant_id = u.id JOIN salles sal ON s.salle_id = sal.id ORDER BY s.date_soutenance ASC")->fetchAll();

} catch (PDOException $e) {
    $message = "Erreur Chargement Données : " . $e->getMessage(); $msg_type = "danger";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planification | UEMF</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="/Gestion-Soutenances/public/assets/css/style.css">

    <meta name="referrer" content="strict-origin-when-cross-origin">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-uemf sticky-top mb-4 shadow-sm bg-dark navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-university me-2"></i>UEMF PLANNING</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small text-uppercase fw-bold"><i class="fas fa-user-tie me-2"></i>Coordinateur</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-primary"></i>Planification des Soutenances</h4>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-arrow-left me-2"></i>Retour</a>
                <button type="button" class="btn btn-primary shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAutoPlanning">
                    <i class="fas fa-magic me-2"></i>Générer Planning Auto
                </button>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $msg_type ?> shadow-sm border-0 d-flex align-items-center mb-4">
                <i class="fas fa-info-circle me-3 fa-lg"></i>
                <div><?= $message ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark"><i class="fas fa-clock me-2 text-warning"></i>À Planifier</span>
                        <span class="badge bg-warning text-dark border"><?= count($projets_a_planifier) ?></span>
                    </div>
                    <div class="card-body p-0 bg-light">
                        <?php if (empty($projets_a_planifier)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                                <h6 class="fw-bold">Tout est planifié !</h6>
                                <p class="small">Aucun dossier en attente.</p>
                            </div>
                        <?php else: ?>
                            <div class="accordion accordion-flush" id="accPlan">
                                <?php foreach ($projets_a_planifier as $index => $p): ?>
                                    <div class="accordion-item border-0 mb-3 bg-white shadow-sm mx-3 mt-3 rounded overflow-hidden">
                                        <h2 class="accordion-header" id="h<?= $p['id'] ?>">
                                            <button class="accordion-button <?= ($index!==0)?'collapsed':'' ?> fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#c<?= $p['id'] ?>">
                                                <i class="fas fa-user-graduate me-2 text-primary"></i>
                                                <?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?>
                                            </button>
                                        </h2>
                                        <div id="c<?= $p['id'] ?>" class="accordion-collapse collapse <?= ($index===0)?'show':'' ?>" data-bs-parent="#accPlan">
                                            <div class="accordion-body bg-white border-top">
                                                <p class="small text-muted mb-2"><i class="fas fa-book me-1"></i> <?= htmlspecialchars(substr($p['titre'], 0, 50)) ?>...</p>
                                                <form method="POST">
                                                    <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                                    <div class="mb-2">
                                                        <label class="small text-muted fw-bold">Date & Heure</label>
                                                        <input type="datetime-local" name="date_soutenance" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="small text-muted fw-bold">Salle (<?= count($salles_disponibles) ?> Disponibles)</label>
                                                        <select name="salle" class="form-select form-select-sm" required>
                                                            <option value="">Sélectionner une salle...</option>
                                                            <?php foreach ($salles_disponibles as $salle_item): ?>
                                                                <option value="<?= htmlspecialchars($salle_item['id']) ?>"><?= htmlspecialchars($salle_item['nom']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" name="planifier_btn" class="btn btn-success btn-sm w-100 fw-bold">
                                                        <i class="fas fa-check me-2"></i>Valider la soutenance
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
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="fw-bold text-dark"><i class="fas fa-calendar-check me-2 text-success"></i>Planning Confirmé</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4 border-0">Date</th>
                                        <th class="border-0">Étudiant</th>
                                        <th class="border-0">Salle</th>
                                        <th class="border-0">Jury</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($liste_soutenances)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">Aucune soutenance planifiée.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($liste_soutenances as $s): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-primary"><?= date('d/m', strtotime($s['date_soutenance'])) ?></div>
                                                    <div class="small text-muted"><?= date('H:i', strtotime($s['date_soutenance'])) ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['nom'].' '.$s['prenom']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?= htmlspecialchars($s['salle_nom']) ?>
                                                    </span>
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

    <div class="modal fade" id="modalAutoPlanning" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-robot me-2"></i>Générer Planning Automatique</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                      
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" name="session_start" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="run_auto_planning" class="btn btn-primary px-4 fw-bold">
                            <i class="fas fa-play me-2"></i>Lancer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>