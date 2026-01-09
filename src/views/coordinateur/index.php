<?php
// 1. SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Chemins absolus
require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';

// 2. VÉRIFICATION DU RÔLE
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

// 3. FONCTION IP & INFO USER
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}
$user_ip = getUserIP();
$nom_coordo = $_SESSION['user_nom'] ?? 'Coordinateur';

// Vérification BDD
if (!isset($pdo)) { die("Erreur critique : La connexion base de données a échoué."); }

// 4. RÉCUPÉRATION DES DONNÉES (KPIs)
try {
    // KPI 1 : Dossiers en attente
    $nbAttente = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut IN ('inscrit', 'rapport_soumis')")->fetchColumn();
    
    // KPI 2 : Prêts pour soutenance (Validés ou Rapport OK, mais pas encore planifiés)
    $nbPrets = $pdo->query("
        SELECT COUNT(*) FROM projets p 
        WHERE (p.statut = 'valide' OR p.statut = 'pret_soutenance' OR (p.statut = 'valide_encadrant' AND p.rapport_chemin IS NOT NULL AND p.rapport_chemin != '')) 
        AND p.id NOT IN (SELECT projet_id FROM soutenances)
    ")->fetchColumn();
    
    // KPI 3 : Soutenances planifiées
    $nbPlanifiees = $pdo->query("SELECT COUNT(*) FROM soutenances")->fetchColumn();

    // Chart Data : Répartition
    $stmtChart = $pdo->query("SELECT f.code, COUNT(p.id) as total FROM filieres f LEFT JOIN projets p ON p.filiere_id = f.id GROUP BY f.id, f.code");
    $labels = []; $dataCount = [];
    while($row = $stmtChart->fetch()) { 
        $labels[] = $row['code']; $dataCount[] = $row['total']; 
    }

    // Tableau Avancement
    $avancement = $pdo->query("
        SELECT f.code, 
               COUNT(p.id) as total_projets, 
               SUM(CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END) as sout_planifiees 
        FROM filieres f 
        LEFT JOIN projets p ON p.filiere_id = f.id 
        LEFT JOIN soutenances s ON s.projet_id = p.id 
        GROUP BY f.id, f.code
    ")->fetchAll();

    // KPI 4 : Tickets non lus (optionnel, mis à 0 par défaut si table absente)
    $nbTickets = 0;
    try {
        $nbTickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE statut != 'resolu'")->fetchColumn();
    } catch(Exception $e) { $nbTickets = 0; }

} catch (PDOException $e) {
    die("Erreur de chargement des données : " . $e->getMessage());
}

$dateDuJour = date('d M Y');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Pilotage PFE | UEMF</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="/Gestion-Soutenances/public/assets/css/style.css">

    <style>
        /* Optionnel : masquer le contenu avant le chargement pour éviter le FOUC (Flash of Unstyled Content) */
        body { display: block !important; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="#">UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-user-tie me-2"></i><?= htmlspecialchars($nom_coordo) ?></span>
                <a href="../auth/logout.php" class="text-white hover-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero bg-primary text-white pb-5 pt-4 mb-5" style="background: linear-gradient(135deg, #004d99 0%, #00264d 100%);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-2 fw-bold">Tableau de Bord</h2>
                    <p class="mb-0 opacity-75">Situation des PFE au <strong><?= $dateDuJour ?></strong>.</p>
                    <div class="mt-3">
                         <span class="badge bg-white text-primary me-2"><i class="fas fa-shield-alt me-1"></i>Admin</span>
                         <span class="badge bg-info text-dark opacity-75"><i class="fas fa-network-wired me-1"></i>IP: <?= $user_ip ?></span>
                    </div>
                </div>
                <div class="d-none d-md-block text-end">
                    <a href="affectation.php" class="btn btn-outline-warning btn-sm border-2 fw-bold">
                        <i class="fas fa-magic me-2"></i>IA Auto-Affectation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: -3rem;">

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <a href="projets.php" class="text-decoration-none transform-hover">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-uppercase text-muted small fw-bold mb-1">Dossiers reçus</div>
                                    <div class="h2 fw-bold text-dark mb-2"><?= $nbAttente ?></div>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-eye me-1"></i>À valider</span>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="fas fa-inbox fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="planification.php" class="text-decoration-none transform-hover">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-uppercase text-muted small fw-bold mb-1">Prêts à soutenir</div>
                                    <div class="h2 fw-bold text-dark mb-2"><?= $nbPrets ?></div>
                                    <span class="badge bg-success"><i class="fas fa-calendar-plus me-1"></i>À planifier</span>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class="fas fa-clock fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="jurys.php" class="text-decoration-none transform-hover">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-uppercase text-muted small fw-bold mb-1">Planifiées</div>
                                    <div class="h2 fw-bold text-dark mb-2"><?= $nbPlanifiees ?></div>
                                    <span class="badge bg-primary"><i class="fas fa-gavel me-1"></i>Jurys</span>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class="fas fa-calendar-check fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i>État d'avancement par Filière</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4 small border-0">Filière</th>
                                        <th class="text-center small border-0">Volumétrie</th>
                                        <th class="text-center small border-0">Planifiés</th>
                                        <th class="text-end pe-4 small border-0">Progression</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($avancement as $row):
                                        $percent = ($row['total_projets'] > 0) ? round(($row['sout_planifiees']/$row['total_projets'])*100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark"><?= $row['code'] ?></td>
                                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $row['total_projets'] ?> sujets</span></td>
                                        <td class="text-center fw-bold text-primary"><?= $row['sout_planifiees'] ?></td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-3 small fw-bold"><?= $percent ?>%</span>
                                                <div class="progress" style="width: 100px; height: 6px;">
                                                    <div class="progress-bar bg-gradient-primary" style="width: <?= $percent ?>%; background-color: #004d99;"></div>
                                                </div>
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

            <div class="col-lg-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2 text-primary"></i>Répartition</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <div style="width: 100%; height: 250px;">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 bg-white shadow-sm mb-5">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase text-muted fw-bold mb-0"><i class="fas fa-cogs me-2"></i>Administration & Support</h6>
                    <?php if($nbTickets > 0): ?>
                        <span class="badge bg-danger rounded-pill animate-pulse"><?= $nbTickets ?> Alertes</span>
                    <?php endif; ?>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="tickets.php" class="btn btn-outline-danger w-100 text-start mb-2 shadow-sm d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-headset me-2"></i><strong>Tickets Support</strong></span>
                            <span class="badge bg-danger rounded-circle"><i class="fas fa-bell"></i></span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="logs.php" class="btn btn-light border w-100 text-start mb-2 text-secondary">
                            <i class="fas fa-clipboard-list me-2"></i>Journal d'Activité
                        </a>
                    </div>

                    <div class="col-12"><hr class="my-2 opacity-10"></div>

                    <div class="col-md-4">
                        <a href="gestion_etudiants.php" class="btn btn-outline-dark w-100 text-start">
                            <i class="fas fa-user-lock me-2"></i>Reset Étudiants
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="gestion_profs.php" class="btn btn-outline-dark w-100 text-start">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Reset Profs / Staff
                        </a>
                    </div>

                    <div class="col-md-4">
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-primary w-100 text-start dropdown-toggle" type="button" id="importMenu" data-bs-toggle="dropdown">
                                <i class="fas fa-file-csv me-2"></i>Imports CSV
                            </button>
                            <ul class="dropdown-menu w-100 shadow border-0">
                                <li><a class="dropdown-item py-2" href="import_etudiants.php"><i class="fas fa-user-graduate me-2"></i>Importer Étudiants</a></li>
                                <li><a class="dropdown-item py-2" href="import_profs.php"><i class="fas fa-chalkboard-teacher me-2"></i>Importer Professeurs</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dataCount); ?>,
                    backgroundColor: ['#004d99', '#c9a227', '#198754', '#6c757d', '#00264d', '#17a2b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 15, font: { size: 11 } } }
                }
            }
        });
    </script>
</body>
</html>