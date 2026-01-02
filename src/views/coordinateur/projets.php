<?php
session_start();
require_once '../../../config/database.php';

// SECURITE
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

// 1. LOGIQUE : Valider un projet
if (isset($_POST['valider_projet_id'])) {
    $id = $_POST['valider_projet_id'];
    $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?");
    $stmt->execute([$id]);
    $message = " Projet #$id validé avec succès !";
    $msg_type = "success";
}

// 2. LOGIQUE : Supprimer un projet
if (isset($_POST['supprimer_projet_id'])) {
    $id = $_POST['supprimer_projet_id'];
    $stmt = $pdo->prepare("DELETE FROM projets WHERE id = ?");
    $stmt->execute([$id]);
    $message = " Projet #$id supprimé définitivement.";
    $msg_type = "danger";
}

// REQUETE : Liste des projets
$sql = "SELECT p.*, u.nom as nom_etudiant 
        FROM projets p 
        JOIN users u ON p.etudiant_id = u.id 
        ORDER BY p.created_at DESC";
$stmt = $pdo->query($sql);
$projets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion des Projets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <?php include '../layout/navbar_coordinateur.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3> Liste des Projets Soumis</h3>
            <a href="index.php" class="btn btn-secondary">← Retour Dashboard</a>
        </div>

        <?php if(isset($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Étudiant</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($projets as $p): ?>
                        <tr>
                            <td>#<?php echo $p['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['titre']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($p['description'], 0, 50)); ?>...</small>
                            </td>
                            <td><?php echo htmlspecialchars($p['nom_etudiant']); ?></td>
                            <td>
                                <?php if($p['statut'] == 'inscrit'): ?>
                                    <span class="badge bg-warning text-dark">En attente</span>
                                <?php elseif($p['statut'] == 'valide_encadrant'): ?>
                                    <span class="badge bg-success">Validé</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($p['statut']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if($p['statut'] == 'inscrit'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="valider_projet_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled><i class="fas fa-check"></i></button>
                                <?php endif; ?>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir SUPPRIMER ce projet ? Cette action est irréversible.');">
                                    <input type="hidden" name="supprimer_projet_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm ms-1" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>