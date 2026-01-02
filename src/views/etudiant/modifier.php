<?php
session_start();
require_once '../../../config/database.php';

// 1. SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// 2. RÉCUPÉRER LE PROJET EXISTANT
$stmt = $pdo->prepare("SELECT * FROM projets WHERE etudiant_id = ?");
$stmt->execute([$id_etudiant]);
$projet = $stmt->fetch();

// Si pas de projet ou si le projet est déjà validé/bloqué -> Hop, dehors !
if (!$projet || $projet['statut'] !== 'inscrit') {
    header("Location: index.php");
    exit();
}

// 3. TRAITEMENT DE LA MISE À JOUR
if (isset($_POST['update_projet'])) {
    $titre = $_POST['titre'];
    $desc = $_POST['description'];

    // On fait un UPDATE au lieu d'un INSERT
    $update = $pdo->prepare("UPDATE projets SET titre = ?, description = ? WHERE id = ?");
    $update->execute([$titre, $desc, $projet['id']]);

    header("Location: index.php"); // Retour au dashboard
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier mon sujet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <h3 class="mb-4"> Modifier mon Sujet</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label>Titre du projet</label>
                <input type="text" name="titre" class="form-control" required 
                       value="<?php echo htmlspecialchars($projet['titre']); ?>">
            </div>
            
            <div class="mb-3">
                <label>Description détaillée</label>
                <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($projet['description']); ?></textarea>
            </div>
            
            <button type="submit" name="update_projet" class="btn btn-primary w-100">Enregistrer les modifications</button>
            <a href="index.php" class="btn btn-link w-100 mt-2">Annuler</a>
        </form>
    </div>
</body>
</html>