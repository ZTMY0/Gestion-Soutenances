<?php
session_start();
require_once '../../../config/database.php';
// On garde le service pour essayer d'abord la méthode "propre" si elle existe
require_once '../../../src/Services/PlanificationService.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// ---------------------------------------------------------
// 1. TRAITEMENT : AUTO-PLANIFICATION (MODE RANDOMISÉ RÉALISTE)
// ---------------------------------------------------------
if (isset($_POST['run_auto_planning'])) {
    if (!empty($_POST['session_start'])) {
        try {
            $dateDebut = $_POST['session_start'];
            // Durée de la session : 15 jours ouvrés par défaut pour étaler les soutenances
            $plageJours = 15; 

            // --- A. LISTE DES SALLES (NOUVELLE NOMENCLATURE) ---
            $sallesDispo = [
                // Amphithéâtres (Bâtiment-Etage.Type-Numéro)
                'B1-0.Amphithéâtre-1', 
                'B2-0.Amphithéâtre-A',
                'B4-0.Amphithéâtre-2',
                
                // Salles de cours/TP (Bâtiment-Etage.Numéro)
                'B1-1.05', 
                'B1-2.04', 
                'B2-1.12', 
                'B3-2.17', 
                'B3-2.18', 
                'B4-1.01',
                'B4-1.02'
            ];

            // --- B. CRÉNEAUX HORAIRES POSSIBLES ---
            $creneauxHoraires = [
                '08:30:00', 
                '10:15:00', 
                '13:30:00', 
                '15:15:00',
                '17:00:00'
            ];

            // On essaie d'abord le Service IA, sinon on passe au mode simulation
            $planningData = [];
            try {
                $service = new PlanificationService($pdo);
                $dateFin = !empty($_POST['session_end']) ? $_POST['session_end'] : date('Y-m-d', strtotime($dateDebut . ' + 30 days'));
                $resultat = $service->genererPlanningAutomatique($dateDebut, $dateFin);
                $planningData = isset($resultat['planning']) ? $resultat['planning'] : (is_array($resultat) ? $resultat : []);
            } catch (Exception $e) { $planningData = []; }

            // --- C. PLAN B : GÉNÉRATION ALÉATOIRE CONTRÔLÉE ---
            if (empty($planningData)) {
                
                // 1. Récupérer les projets
                $sqlRecup = "SELECT p.id FROM projets p 
                             WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR p.statut = 'valide_encadrant')
                             AND p.id NOT IN (SELECT projet_id FROM soutenances)";
                $projetsManuels = $pdo->query($sqlRecup)->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($projetsManuels)) {
                    
                    // Tableau pour suivre les créneaux occupés (éviter qu'une salle soit prise 2 fois à la même heure)
                    // Format clé: "YYYY-MM-DD_HH:MM:SS_NomSalle"
                    $slotsOccupes = []; 

                    foreach ($projetsManuels as $pid) {
                        $creneauTrouve = false;
                        $tentatives = 0;

                        // On essaie jusqu'à trouver un créneau libre (max 50 essais pour éviter boucle infinie)
                        while (!$creneauTrouve && $tentatives < 50) {
                            $tentatives++;

                            // 1. Choisir un jour aléatoire (+0 à +15 jours)
                            $randDay = rand(0, $plageJours);
                            $testDate = date('Y-m-d', strtotime($dateDebut . " + $randDay days"));
                            
                            // Si c'est un Samedi (6) ou Dimanche (7), on saute
                            if (date('N', strtotime($testDate)) >= 6) continue;

                            // 2. Choisir une heure aléatoire
                            $testHeure = $creneauxHoraires[array_rand($creneauxHoraires)];
                            
                            // 3. Choisir une salle aléatoire
                            $testSalle = $sallesDispo[array_rand($sallesDispo)];

                            // 4. Vérifier collision
                            $cleUnique = $testDate . '_' . $testHeure . '_' . $testSalle;

                            if (!isset($slotsOccupes[$cleUnique])) {
                                // C'est libre ! On réserve.
                                $slotsOccupes[$cleUnique] = true;
                                $planningData[] = [
                                    'projet_id' => $pid,
                                    'date_soutenance' => $testDate . ' ' . $testHeure,
                                    'salle' => $testSalle
                                ];
                                $creneauTrouve = true;
                            }
                        }
                    }
                }
            }

            // --- D. INSERTION EN BASE ---
            $count = 0;
            if (!empty($planningData)) {
                $stmtInsert = $pdo->prepare("INSERT INTO soutenances (projet_id, date_soutenance, salle) VALUES (?, ?, ?)");
                $stmtUpdate = $pdo->prepare("UPDATE projets SET statut = 'valide' WHERE id = ?");

                foreach ($planningData as $creneau) {
                    $check = $pdo->prepare("SELECT id FROM soutenances WHERE projet_id = ?");
                    $check->execute([$creneau['projet_id']]);
                    
                    if ($check->rowCount() == 0) {
                        $stmtInsert->execute([
                            $creneau['projet_id'],
                            $creneau['date_soutenance'],
                            $creneau['salle']
                        ]);
                        $stmtUpdate->execute([$creneau['projet_id']]);
                        $count++;
                    }
                }
            }

            if ($count > 0) {
                $message = "<strong>Succès !</strong> $count soutenance(s) réparties aléatoirement sur les campus.";
                $msg_type = "success";
            } else {
                $message = "Aucun projet à planifier ou tous les projets sont déjà casés.";
                $msg_type = "warning";
            }

        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $msg_type = "danger";
        }
    } else {
        $message = "Veuillez sélectionner une date de début.";
        $msg_type = "warning";
    }
}

// ---------------------------------------------------------
// 2. TRAITEMENT MANUEL
// ---------------------------------------------------------
if (isset($_POST['planifier_btn'])) {
    $projet_id = $_POST['projet_id'];
    $date = $_POST['date_soutenance'];
    $salle = $_POST['salle'];
    
    $check = $pdo->prepare("SELECT id FROM soutenances WHERE projet_id = ?");
    $check->execute([$projet_id]);
    
    if ($check->rowCount() > 0) {
        $message = "Déjà planifié."; $msg_type = "warning";
    } else {
        $pdo->prepare("INSERT INTO soutenances (projet_id, date_soutenance, salle) VALUES (?, ?, ?)")->execute([$projet_id, $date, $salle]);
        $pdo->prepare("UPDATE projets SET statut = 'valide' WHERE id = ?")->execute([$projet_id]);
        $message = "Enregistré."; $msg_type = "success";
    }
}

// 3. DONNÉES
$sql = "SELECT p.*, u.nom, u.prenom 
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR (p.statut = 'valide_encadrant' AND p.rapport_chemin IS NOT NULL))
        AND p.id NOT IN (SELECT projet_id FROM soutenances)";
$projets_a_planifier = $pdo->query($sql)->fetchAll();

$liste_soutenances = $pdo->query("SELECT s.*, p.titre, u.nom, u.prenom FROM soutenances s JOIN projets p ON s.projet_id = p.id JOIN users u ON p.etudiant_id = u.id ORDER BY s.date_soutenance ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Planification | UEMF</title>
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
            <h4 class="fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Planification</h4>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-arrow-left me-2"></i>Retour</a>
                <button type="button" class="btn btn-magic shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalAutoPlanning">
        </i>Générer Planning Auto
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
                        <span class="fw-bold">À Planifier</span>
                        <span class="badge bg-warning text-dark border"><?= count($projets_a_planifier) ?></span>
                    </div>
                    <div class="card-body p-0 bg-light">
                        <?php if (empty($projets_a_planifier)): ?>
                            <div class="text-center py-5 text-muted"><h6 class="fw-bold">Aucun dossier en attente</h6></div>
                        <?php else: ?>
                            <div class="accordion accordion-flush" id="accPlan">
                                <?php foreach ($projets_a_planifier as $index => $p): ?>
                                    <div class="accordion-item border-0 mb-3 bg-white shadow-sm mx-3 mt-3 rounded">
                                        <h2 class="accordion-header" id="h<?= $p['id'] ?>">
                                            <button class="accordion-button <?= ($index!==0)?'collapsed':'' ?> fw-bold text-dark bg-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#c<?= $p['id'] ?>">
                                                <?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?>
                                            </button>
                                        </h2>
                                        <div id="c<?= $p['id'] ?>" class="accordion-collapse collapse <?= ($index===0)?'show':'' ?>" data-bs-parent="#accPlan">
                                            <div class="accordion-body pt-0 pb-3 px-3">
                                                <form method="POST">
                                                    <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                                    <div class="mb-2"><input type="datetime-local" name="date_soutenance" class="form-control form-control-sm" required></div>
                                                    <div class="mb-3">
                                                        <select name="salle" class="form-select form-select-sm" required>
                                                            <option value="">Salle...</option>
                                                            <option value="B4-0.Amphithéâtre-2">B4-0.Amphithéâtre-2</option>
                                                            <option value="B3-2.17">B3-2.17</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" name="planifier_btn" class="btn btn-success btn-sm w-100">Valider</button>
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
                        <span class="fw-bold">Calendrier Officiel</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light"><tr><th class="ps-4">Date</th><th>Étudiant</th><th>Salle</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($liste_soutenances as $s): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-primary"><?= date('d/m', strtotime($s['date_soutenance'])) ?></div>
                                                <div class="small text-muted"><?= date('H:i', strtotime($s['date_soutenance'])) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($s['nom'].' '.$s['prenom']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['salle']) ?></span></td>
                                            <td class="text-end pe-4"><a href="jurys.php?soutenance_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-gavel"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
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
                    <h5 class="modal-title">Générer Planning</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Début des soutenances</label>
                            <input type="date" name="session_start" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="run_auto_planning" class="btn btn-primary w-100">Lancer l'Algorithme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>