<?php
session_start();
require_once '../../../config/database.php';

// Check auth rapide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

// Pas d'ID ? On dégage
if (!isset($_GET['id'])) {
    header("Location: projets.php");
    exit();
}

$id_projet = $_GET['id'];
$msg = "";
$msg_type = "";

// --- TRAITEMENT DES FORMULAIRES ---

// 1. Affectation Encadrant
if (isset($_POST['action']) && $_POST['action'] === 'affecter') {
    if (!empty($_POST['encadrant_id'])) {
        $stmt = $pdo->prepare("UPDATE projets SET encadrant_id = ? WHERE id = ?");
        $stmt->execute([$_POST['encadrant_id'], $id_projet]);
        
        $msg = "Encadrant mis à jour avec succès.";
        $msg_type = "success";
    }
}

// 2. Validation du sujet
if (isset($_POST['action']) && $_POST['action'] === 'valider') {
    $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?");
    $stmt->execute([$id_projet]);
    
    $msg = "Sujet validé. L'étudiant peut commencer.";
    $msg_type = "success";
}

// --- RECUPERATION DES DONNEES ---

// 1. Infos du projet + Etudiant + Encadrant actuel
$sql = "SELECT p.*, 
               u.nom AS etu_nom, u.prenom AS etu_prenom, u.email AS etu_email,
               prof.id AS prof_id, prof.nom AS prof_nom, prof.prenom AS prof_prenom
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        LEFT JOIN users prof ON p.encadrant_id = prof.id
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_projet]);
$projet = $stmt->fetch();

if (!$projet) die("Erreur : Projet introuvable.");

// 2. Liste des profs (Triés par charge de travail : du - occupé au + occupé)
// On compte le nombre de projets où ils sont déjà encadrants
$sqlProfs = "SELECT u.id, u.nom, u.prenom, COUNT(p.id) as total_projets
             FROM users u
             LEFT JOIN projets p ON u.id = p.encadrant_id
             WHERE u.role = 'prof'
             GROUP BY u.id
             ORDER BY total_projets ASC, u.nom ASC";
$liste_profs = $pdo->query($sqlProfs)->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Détails Projet #<?php echo $projet['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge-tech { background-color: #6c757d; margin-right: 5px; }
        .bg-gradient-primary { background: linear-gradient(45deg, #0d6efd, #0a58ca); }
    </style>
</head>
<body class="bg-light">

    <?php include '../layout/navbar_coordinateur.php'; ?>

    <div class="container mt-4 mb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="projets.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <span class="text-muted small">Soumis le <?php echo date('d/m/Y', strtotime($projet['created_at'])); ?></span>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white p-4">
                <div class="d-flex justify-content-between">
                    <h3 class="h4 mb-0"><?php echo htmlspecialchars($projet['titre']); ?></h3>
                    
                    <?php 
                        $badges = [
                            'inscrit' => 'bg-warning text-dark',
                            'valide_encadrant' => 'bg-success',
                            'rejeté' => 'bg-danger'
                        ];
                        $css = $badges[$projet['statut']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?php echo $css; ?> fs-6 align-self-center">
                        <?php echo strtoupper($projet['statut']); ?>
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    
                    <div class="col-md-8">
                        <h5 class="text-primary border-bottom pb-2">Description</h5>
                        <p class="text-secondary" style="white-space: pre-line;"><?php echo htmlspecialchars($projet['description']); ?></p>

                        <h5 class="text-primary border-bottom pb-2 mt-4">Technologies</h5>
                        <div class="mb-3">
                            <?php 
                            if(!empty($projet['technologies'])):
                                $tags = explode(',', $projet['technologies']);
                                foreach($tags as $tag): 
                            ?>
                                <span class="badge rounded-pill badge-tech"><?php echo trim(htmlspecialchars($tag)); ?></span>
                            <?php 
                                endforeach;
                            else: 
                                echo "<span class='text-muted small'>Aucune technologie spécifiée</span>";
                            endif;
                            ?>
                        </div>

                        <?php if(!empty($projet['resume'])): ?>
                            <div class="alert alert-light border mt-4">
                                <strong>Résumé / Abstract :</strong><br>
                                <em class="small"><?php echo nl2br(htmlspecialchars($projet['resume'])); ?></em>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        
                        <div class="card bg-light mb-3 border-0">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small fw-bold">Étudiant</h6>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($projet['etu_nom'] . ' ' . $projet['etu_prenom']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($projet['etu_email']); ?></div>
                                    </div>
                                </div>
                                
                                <?php if(!empty($projet['binome_email'])): ?>
                                    <hr>
                                    <h6 class="text-uppercase text-muted small fw-bold">Binôme</h6>
                                    <div class="small"><i class="fas fa-user-friends me-1"></i> <?php echo htmlspecialchars($projet['binome_email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card border-primary mb-3">
                            <div class="card-header bg-white text-primary fw-bold">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Encadrement
                            </div>
                            <div class="card-body">
                                
                                <?php if($projet['prof_id']): ?>
                                    <div class="alert alert-success py-2 mb-3">
                                        <small>Actuel :</small><br>
                                        <strong>M. <?php echo htmlspecialchars($projet['prof_nom']); ?></strong>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2 mb-3 text-center">
                                        <small>⚠️ Aucun encadrant affecté</small>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <input type="hidden" name="action" value="affecter">
                                    <label class="form-label small fw-bold">Affecter / Changer :</label>
                                    
                                    <select name="encadrant_id" class="form-select form-select-sm mb-2" required>
                                        <option value="">-- Choisir un prof --</option>
                                        
                                        <?php foreach($liste_profs as $p): ?>
                                            <?php 
                                                // Logique de couleur selon la charge
                                                $nb = $p['total_projets'];
                                                $style = "";
                                                $icon = "🟢"; // Vert par défaut

                                                if($nb >= 3 && $nb < 6) {
                                                    $icon = "🟠"; // Orange
                                                    $style = "color: #d35400;";
                                                } elseif($nb >= 6) {
                                                    $icon = "🔴"; // Rouge
                                                    $style = "color: red; font-weight: bold;";
                                                }
                                                
                                                // Pré-sélection
                                                $selected = ($p['id'] == $projet['prof_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo $p['id']; ?>" <?php echo $selected; ?> style="<?php echo $style; ?>">
                                                <?php echo "$icon " . htmlspecialchars($p['nom'] . " " . $p['prenom']) . " ($nb projets)"; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <button type="submit" class="btn btn-dark btn-sm w-100">Enregistrer l'affectation</button>
                                </form>
                            </div>
                        </div>

                        <?php if($projet['statut'] == 'inscrit'): ?>
                            <hr>
                            <form method="POST">
                                <input type="hidden" name="action" value="valider">
                                <button type="submit" class="btn btn-success w-100 fw-bold" onclick="return confirm('Confirmer la validation du sujet ?')">
                                    <i class="fas fa-check-circle me-2"></i>Valider le Sujet
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>