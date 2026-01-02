<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

$id_etudiant = $_SESSION['user_id'];
$projet = null;

// REQUÊTE AMÉLIORÉE (JOIN)
// On récupère le projet ET le nom de l'encadrant en une seule fois
$sql = "SELECT p.*, u.nom AS nom_encadrant 
        FROM projets p 
        LEFT JOIN users u ON p.encadrant_id = u.id 
        WHERE p.etudiant_id = ?";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_etudiant]);
$projet = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Mon Espace PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow-sm">
        <span class="navbar-brand mb-0 h1"><i class="fas fa-graduation-cap me-2"></i>Mon PFE</span>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 d-none d-md-block">Bonjour, <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
            <a href="../auth/logout.php" class="btn btn-sm btn-light text-primary fw-bold">Déconnexion</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <?php if (!$projet): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50">
                <h2 class="text-muted">Aucun projet en cours</h2>
                <p class="lead mb-4">La campagne de choix des sujets est ouverte.</p>
                <a href="soumettre.php" class="btn btn-primary btn-lg px-5 rounded-pill">
                    <i class="fas fa-plus-circle me-2"></i>Soumettre mon sujet
                </a>
            </div>

        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary fw-bold">
                                <i class="fas fa-folder-open me-2"></i><?php echo htmlspecialchars($projet['titre']); ?>
                            </h5>
                            <?php if($projet['statut'] == 'inscrit'): ?>
                                <a href="modifier.php" class="btn btn-outline-secondary btn-sm" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <?php 
                                    $badges = [
                                        'inscrit' => ['bg-warning', 'En attente validation'],
                                        'valide_encadrant' => ['bg-info', 'Validé par Encadrant'],
                                        'planifie' => ['bg-primary', 'Soutenance Planifiée'],
                                        'soutenu' => ['bg-success', 'Projet Soutenu']
                                    ];
                                    $statusInfo = $badges[$projet['statut']] ?? ['bg-secondary', $projet['statut']];
                                ?>
                                <span class="badge <?php echo $statusInfo[0]; ?> fs-6">
                                    <?php echo $statusInfo[1]; ?>
                                </span>
                            </div>

                            <h6 class="text-muted text-uppercase small fw-bold">Description</h6>
                            <p class="text-secondary"><?php echo nl2br(htmlspecialchars($projet['description'])); ?></p>
                            
                            <h6 class="text-muted text-uppercase small fw-bold mt-3">Technologies</h6>
                            <p class="text-dark"><i class="fas fa-code me-1"></i> <?php echo htmlspecialchars($projet['technologies'] ?? 'Non spécifié'); ?></p>

                            <?php if(!empty($projet['binome_email'])): ?>
                                <h6 class="text-muted text-uppercase small fw-bold mt-3">Binôme</h6>
                                <p class="text-dark"><i class="fas fa-user-friends me-1"></i> <?php echo htmlspecialchars($projet['binome_email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Encadrant Affecté</h6>
                            <?php if ($projet['nom_encadrant']): ?>
                                <div class="avatar bg-success text-white rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:60px; height:60px; font-size:24px;">
                                    <?php echo strtoupper(substr($projet['nom_encadrant'], 0, 1)); ?>
                                </div>
                                <h5 class="fw-bold"><?php echo htmlspecialchars($projet['nom_encadrant']); ?></h5>
                                <a href="mailto:encadrant@uemf.ma" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-envelope me-1"></i>Contacter
                                </a>
                            <?php else: ?>
                                <div class="text-muted py-3">
                                    <i class="fas fa-user-clock fa-3x mb-2 opacity-25"></i>
                                    <p class="mb-0">En attente d'affectation...</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if($projet['encadrant_id']): ?>
                            <a href="depot.php" class="btn btn-success">
                                <i class="fas fa-file-pdf me-2"></i>Déposer mon Rapport
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-lock me-2"></i>Dépôt Rapport (Verrouillé)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>