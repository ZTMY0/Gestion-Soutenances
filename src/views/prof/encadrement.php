<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ : Vérifier que c'est bien un prof connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// TRAITEMENT : Validation du rapport
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_rapport'])) {
    $projet_id = intval($_POST['projet_id']);
    
    // Vérifier que ce projet appartient bien à ce prof
    $stmt = $pdo->prepare("SELECT id FROM projets WHERE id = ? AND encadrant_id = ?");
    $stmt->execute([$projet_id, $prof_id]);
    
    if ($stmt->fetch()) {
        // Mettre à jour le statut du projet
        $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?");
        $stmt->execute([$projet_id]);
        
        $message = "Le rapport a été validé avec succès. L'étudiant peut maintenant passer en soutenance.";
        $messageType = "success";
    } else {
        $message = "Erreur : Ce projet ne vous est pas assigné.";
        $messageType = "danger";
    }
}

// RÉCUPÉRER LES PROJETS ENCADRÉS
$sql = "SELECT p.*, 
               u.nom AS etudiant_nom, 
               u.email AS etudiant_email,
               b.nom AS binome_nom,
               f.nom AS filiere_nom,
               r.chemin_fichier AS rapport_path,
               r.date_upload AS rapport_date,
               r.id AS rapport_id
        FROM projets p
        JOIN users u ON p.etudiant_id = u.id
        LEFT JOIN users b ON p.binome_id = b.id
        LEFT JOIN filieres f ON p.filiere_id = f.id
        LEFT JOIN rapports r ON r.projet_id = p.id
        WHERE p.encadrant_id = ?
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$prof_id]);
$projets = $stmt->fetchAll();

// Statistiques
$nbTotal = count($projets);
$nbEnAttente = 0;
$nbValides = 0;
foreach ($projets as $p) {
    if ($p['statut'] === 'valide_encadrant' || $p['statut'] === 'planifie' || $p['statut'] === 'soutenu') {
        $nbValides++;
    } elseif ($p['rapport_path']) {
        $nbEnAttente++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Encadrements - Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .project-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .project-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .stat-card {
            border-radius: 12px;
            border: none;
        }
        .btn-validate {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
        }
        .btn-validate:hover {
            background: linear-gradient(135deg, #0f8a7e 0%, #32d970 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <div class="d-flex align-items-center">
            <a href="index.php" class="navbar-brand mb-0 h1">
                <i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur
            </a>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-block">
                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_nom']); ?>
            </span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-user-graduate text-primary me-2"></i>Mes Encadrements</h2>
                <p class="text-muted mb-0">Suivez vos étudiants et validez leurs rapports</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>

        <!-- MESSAGE -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- STATISTIQUES -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fas fa-users fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbTotal; ?></h3>
                            <small class="text-muted">Projets encadrés</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="fas fa-hourglass-half fa-lg text-warning"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbEnAttente; ?></h3>
                            <small class="text-muted">Rapports à valider</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-check-circle fa-lg text-success"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbValides; ?></h3>
                            <small class="text-muted">Projets validés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTE DES PROJETS -->
        <?php if (count($projets) === 0): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <i class="fas fa-folder-open fa-4x text-muted mb-3 opacity-50"></i>
                <h4 class="text-muted">Aucun projet encadré</h4>
                <p class="text-muted">Vous n'avez pas encore d'étudiants assignés.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($projets as $projet): ?>
                    <?php
                    // Définir le badge de statut
                    $statusConfig = [
                        'inscrit' => ['bg-secondary', 'En attente', 'fa-clock'],
                        'encadrant_affecte' => ['bg-info', 'Affecté', 'fa-user-check'],
                        'rapport_soumis' => ['bg-warning', 'Rapport soumis', 'fa-file-pdf'],
                        'valide_encadrant' => ['bg-success', 'Validé', 'fa-check'],
                        'planifie' => ['bg-primary', 'Planifié', 'fa-calendar'],
                        'soutenu' => ['bg-dark', 'Soutenu', 'fa-graduation-cap']
                    ];
                    $status = $statusConfig[$projet['statut']] ?? ['bg-secondary', $projet['statut'], 'fa-question'];
                    $initiales = strtoupper(substr($projet['etudiant_nom'], 0, 2));
                    ?>
                    <div class="col-lg-6">
                        <div class="card project-card shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="student-avatar me-3"><?php echo $initiales; ?></div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($projet['etudiant_nom']); ?></h6>
                                            <?php if ($projet['binome_nom']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i>Binôme: <?php echo htmlspecialchars($projet['binome_nom']); ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>Monôme
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="badge <?php echo $status[0]; ?> status-badge">
                                        <i class="fas <?php echo $status[2]; ?> me-1"></i><?php echo $status[1]; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($projet['titre']); ?></h5>
                                
                                <?php if ($projet['description']): ?>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($projet['description'], 0, 150)); ?>...
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($projet['filiere_nom'] ?? 'N/A'); ?>
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($projet['annee_universitaire']); ?>
                                    </span>
                                </div>

                                <!-- RAPPORT -->
                                <?php if ($projet['rapport_path']): ?>
                                    <div class="alert alert-light border mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                <strong>Rapport déposé</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($projet['rapport_date'])); ?>
                                                </small>
                                            </div>
                                            <a href="../../../public/<?php echo htmlspecialchars($projet['rapport_path']); ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank"
                                               download>
                                                <i class="fas fa-download me-1"></i>Télécharger
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-secondary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Aucun rapport déposé pour le moment.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ACTIONS -->
                            <div class="card-footer bg-white border-top-0 pb-3">
                                <div class="d-flex gap-2">
                                    <a href="mailto:<?php echo htmlspecialchars($projet['etudiant_email']); ?>" 
                                       class="btn btn-outline-secondary btn-sm flex-fill">
                                        <i class="fas fa-envelope me-1"></i>Contacter
                                    </a>
                                    
                                    <?php if ($projet['rapport_path'] && $projet['statut'] !== 'valide_encadrant' && $projet['statut'] !== 'planifie' && $projet['statut'] !== 'soutenu'): ?>
                                        <form method="POST" class="flex-fill" onsubmit="return confirm('Êtes-vous sûr de vouloir valider ce rapport ? Cette action autorisera l\'étudiant à passer en soutenance.');">
                                            <input type="hidden" name="projet_id" value="<?php echo $projet['id']; ?>">
                                            <button type="submit" name="valider_rapport" class="btn btn-validate btn-sm w-100">
                                                <i class="fas fa-check-circle me-1"></i>Valider le rapport
                                            </button>
                                        </form>
                                    <?php elseif ($projet['statut'] === 'valide_encadrant'): ?>
                                        <button class="btn btn-success btn-sm flex-fill" disabled>
                                            <i class="fas fa-check me-1"></i>Déjà validé
                                        </button>
                                    <?php elseif ($projet['statut'] === 'planifie' || $projet['statut'] === 'soutenu'): ?>
                                        <button class="btn btn-dark btn-sm flex-fill" disabled>
                                            <i class="fas fa-calendar-check me-1"></i><?php echo $projet['statut'] === 'soutenu' ? 'Soutenu' : 'Planifié'; ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm flex-fill" disabled>
                                            <i class="fas fa-hourglass me-1"></i>En attente du rapport
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
