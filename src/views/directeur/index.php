<?php
// 1. SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 0); // En prod
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Chemins absolus
require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';

// 2. VÉRIFICATION DU RÔLE
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php"); exit();
}

// 3. FONCTION IP & INFOS
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}
$user_ip = getUserIP();
$nom_dir = $_SESSION['user_nom'] ?? 'Directeur';

// Vérification BDD
if (!isset($pdo)) { die("Erreur critique : La connexion base de données a échoué."); }

// 4. RÉCUPÉRATION DES KPI (Indicateurs Clés de Performance)
try {
    // KPI 1 : Volumétrie Étudiants
    $nbEtudiants = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'etudiant'")->fetchColumn();

    // KPI 2 : Taux de Validation Globale (CORRIGÉ)
    $nbProjets = $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn();
    
    // CORRECTION ICI : On compte uniquement ceux qui ont le statut 'valide'
    $nbValides = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'valide'")->fetchColumn();
    
    $taux = ($nbProjets > 0) ? round(($nbValides / $nbProjets) * 100) : 0;

    // KPI 3 : Alertes (Projets encadrés mais sans rapport déposé => Retard potentiel)
    $nbAlertes = $pdo->query("
        SELECT COUNT(*)
        FROM projets p
        WHERE p.statut = 'encadre' 
        AND (p.rapport_chemin IS NULL OR p.rapport_chemin = '')
    ")->fetchColumn();

    // KPI 4 : Données pour le Graphique (Statuts des projets)
    $stmtStats = $pdo->query("SELECT statut, COUNT(*) as count FROM projets GROUP BY statut");
    $chartLabels = [];
    $chartData = [];
    while($row = $stmtStats->fetch()) {
        $chartLabels[] = ucfirst(str_replace('_', ' ', $row['statut']));
        $chartData[] = $row['count'];
    }

} catch (PDOException $e) {
    die("Erreur de chargement des statistiques : " . $e->getMessage());
}

$dateDuJour = date('d M Y');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Direction Générale | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="#">UEMF DIRECTION</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-user-shield me-2"></i><?= htmlspecialchars($nom_dir) ?></span>
                <a href="../auth/logout.php" class="text-white hover-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero bg-primary text-white pb-5 pt-4 mb-5" style="background: linear-gradient(135deg, #1a1a1a 0%, #4a4a4a 100%);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-2 fw-bold">Vue d'ensemble</h2>
                    <p class="mb-0 opacity-75">Pilotage stratégique des PFE - Situation au <strong><?= $dateDuJour ?></strong>.</p>
                    
                    <div class="mt-3">
                        <span class="badge bg-white text-dark me-2">
                            <i class="fas fa-building me-1"></i>Direction
                        </span>
                        <span class="badge bg-secondary opacity-75">
                            <i class="fas fa-network-wired me-1"></i>IP: <?= $user_ip ?>
                        </span>
                    </div>
                </div>
                <div class="d-none d-md-block text-end opacity-50">
                    <i class="fas fa-university fa-4x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: -3rem;">

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold mb-1">Total Étudiants</div>
                                <div class="h2 fw-bold text-dark mb-2"><?= $nbEtudiants ?></div>
                                <span class="badge bg-primary bg-opacity-10 text-primary">Inscrits</span>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold mb-1">Taux de Réussite</div>
                                <div class="h2 fw-bold text-dark mb-2"><?= $taux ?>%</div>
                                <span class="badge bg-success bg-opacity-10 text-success">Projets Validés</span>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold mb-1">Points de Vigilance</div>
                                <div class="h2 fw-bold text-dark mb-2"><?= $nbAlertes ?></div>
                                <span class="badge bg-warning bg-opacity-10 text-warning text-dark">Retards Rapports</span>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            
            <div class="col-lg-8">
                <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">
                    <i class="fas fa-cogs text-dark me-2"></i>Gestion Stratégique
                </h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <a href="validation.php" class="text-decoration-none">
                            <div class="card shadow-sm border-0 h-100 hover-shadow transition-all">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                                    <h5 class="text-dark fw-bold">Validation Planning</h5>
                                    <p class="text-muted small">Approuver les dates de soutenances proposées par les coordinateurs.</p>
                                    <span class="btn btn-outline-primary btn-sm rounded-pill px-4">Accéder</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-6">
                        <a href="signatures.php" class="text-decoration-none">
                            <div class="card shadow-sm border-0 h-100 hover-shadow transition-all">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-file-signature fa-3x text-success mb-3"></i>
                                    <h5 class="text-dark fw-bold">Signature PV</h5>
                                    <p class="text-muted small">Signer électroniquement les procès-verbaux de délibération.</p>
                                    <span class="btn btn-outline-success btn-sm rounded-pill px-4">Signer</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-12">
                        <a href="parametres.php" class="text-decoration-none">
                            <div class="card shadow-sm border-0 h-100 hover-shadow transition-all bg-white">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-3 rounded-circle me-3 text-secondary">
                                            <i class="fas fa-sliders-h fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-dark fw-bold mb-1">Paramètres Système</h5>
                                            <p class="text-muted small mb-0">Délais, configuration des années universitaires et archivage.</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                 <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">
                    <i class="fas fa-chart-pie text-dark me-2"></i>Répartition
                </h5>
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <div style="width: 100%; height: 300px;">
                            <canvas id="directorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Graphique Director
        const ctx = document.getElementById('directorChart').getContext('2d');
        new Chart(ctx, {
            type: 'polarArea', 
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Projets',
                    data: <?php echo json_encode($chartData); ?>,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)', // Bleu
                        'rgba(255, 206, 86, 0.7)', // Jaune
                        'rgba(75, 192, 192, 0.7)', // Vert
                        'rgba(255, 99, 132, 0.7)', // Rouge
                        'rgba(153, 102, 255, 0.7)' // Violet
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    r: { ticks: { display: false } }
                }
            }
        });
    </script>
</body>
</html>