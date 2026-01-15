<?php
// 1. SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';
// Inclusion du service de sécurité
require_once __DIR__ . '/../../services/SecurityService.php'; 
$security = new SecurityService($pdo);

// Vérification du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// 2. TRAITEMENT DES ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité (CSRF). Veuillez actualiser la page.");
    }

    // Validation
    if (isset($_POST['valider_projet_id'])) {
        $id = (int)$_POST['valider_projet_id'];
        if($id > 0) {
            $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Audit Log
            $security->logAction($_SESSION['user_id'], 'COORD_VALIDATION', "Validation projet ID $id");
            
            $message = "Projet validé avec succès."; 
            $msg_type = "success";
        }
    }
    
    // Suppression
    if (isset($_POST['supprimer_projet_id'])) {
        $id = (int)$_POST['supprimer_projet_id'];
        if($id > 0) {
            // Récupérer le fichier pour le supprimer du disque
            $stmtFile = $pdo->prepare("SELECT rapport_chemin FROM projets WHERE id = ?");
            $stmtFile->execute([$id]);
            $file = $stmtFile->fetchColumn();
            
            if($file && file_exists(__DIR__ . '/../../../public/uploads/' . $file)) {
                unlink(__DIR__ . '/../../../public/uploads/' . $file);
            }

            $pdo->prepare("DELETE FROM projets WHERE id = ?")->execute([$id]);
            
            // Audit Log
            $security->logAction($_SESSION['user_id'], 'COORD_SUPPRESSION', "Suppression projet ID $id");

            $message = "Projet supprimé définitivement."; 
            $msg_type = "danger";
        }
    }
}

// 3. RÉCUPÉRATION DES PROJETS (AVEC JOINTURE FILIERE)
// CORRECTION ICI : On ajoute le JOIN vers la table 'filieres' pour avoir le nom (ex: Cybersécurité)
// au lieu de l'ID (ex: 2).
$sql = "SELECT p.*, 
               u.nom as nom_etudiant, 
               u.prenom as prenom_etudiant, 
               f.nom as nom_filiere 
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        LEFT JOIN filieres f ON u.filiere_id = f.id 
        ORDER BY FIELD(p.statut, 'inscrit') DESC, p.created_at DESC";

try {
    $projets = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Fallback si la table 'filieres' n'existe pas encore pour éviter le crash
    // On reprend l'ancienne requête sans la filière
    $sql_fallback = "SELECT p.*, u.nom as nom_etudiant, u.prenom as prenom_etudiant 
                     FROM projets p 
                     JOIN users u ON p.etudiant_id = u.id 
                     ORDER BY FIELD(p.statut, 'inscrit') DESC, p.created_at DESC";
    $projets = $pdo->query($sql_fallback)->fetchAll();
}

// 4. STATISTIQUES RAPIDES
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Projets | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Gestion-Soutenances/public/assets/css/style.css">
</head>
<body class="bg-light">

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

    <div class="container pb-5 mt-n5" style="position: relative; z-index: 2;">
        
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small fw-bold text-muted">Total Dossiers</div>
                            <div class="h2 mb-0 text-primary"><?= $total ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small fw-bold text-warning">En Attente</div>
                            <div class="h2 mb-0 text-warning"><?= $en_attente ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small fw-bold text-success">Validés</div>
                            <div class="h2 mb-0 text-success"><?= $valides ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $msg_type ?> border-0 shadow-sm mb-4"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="Filtrer par nom ou sujet..." onkeyup="filterTable()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="projectsTable">
                        <thead class="bg-light text-muted text-uppercase small">
                            <tr>
                                <th class="ps-4">Étudiant</th>
                                <th>Sujet</th>
                                <th>Rapport</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($projets as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 38px; height: 38px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($p['nom_etudiant'],0,1).substr($p['prenom_etudiant'],0,1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['nom_etudiant'].' '.$p['prenom_etudiant']) ?></div>
                                                
                                                <div class="small text-muted">
                                                    <?php 
                                                        // Si la jointure a marché, on affiche le nom (ex: M2 Cyber)
                                                        // Sinon, on affiche une valeur par défaut
                                                        echo htmlspecialchars($p['nom_filiere'] ?? 'M2 Cybersécurité'); 
                                                    ?>
                                                </div>

                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <div class="fw-bold text-primary text-truncate"><?= htmlspecialchars($p['titre']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 280px;"><?= htmlspecialchars($p['description']) ?></div>
                                    </td>
                                    
                                    <td>
                                        <?php if(!empty($p['rapport_chemin'])): ?>
                                            <a href="../download.php?f=<?= urlencode(basename($p['rapport_chemin'])) ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                                <i class="fas fa-file-pdf me-1"></i>PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Aucun</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if($p['statut'] == 'inscrit'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>En attente</span>
                                        <?php elseif($p['statut'] == 'valide_encadrant'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Validé</span>
                                        <?php elseif($p['statut'] == 'rapport_soumis'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-file-upload me-1"></i>Rapport Reçu</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($p['statut']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="details_projet.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Détails"><i class="fas fa-eye"></i></a>

                                            <?php if($p['statut'] == 'inscrit'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Valider ce projet ?');">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="valider_projet_id" value="<?= $p['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-success" title="Valider"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Attention : Suppression définitive ! Continuer ?');">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="supprimer_projet_id" value="<?= $p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger ms-1" title="Supprimer"><i class="fas fa-trash"></i></button>
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

    <script>
    function filterTable() {
        var input, filter, table, tr, td, i;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("projectsTable");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) {
            var tdName = tr[i].getElementsByTagName("td")[0];
            var tdTitle = tr[i].getElementsByTagName("td")[1];
            
            if (tdName || tdTitle) {
                var txtName = tdName.textContent || tdName.innerText;
                var txtTitle = tdTitle.textContent || tdTitle.innerText;
                
                if (txtName.toUpperCase().indexOf(filter) > -1 || txtTitle.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }        
        }
    }
    </script>
</body>
</html>