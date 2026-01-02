<?php
session_start();
require_once '../../../config/database.php';

// 1. SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

$etudiant_id = $_SESSION['user_id'];
$message = "";
$error = "";

// 2. RÉCUPÉRER LE PROJET
$stmt = $pdo->prepare("SELECT * FROM projets WHERE etudiant_id = ?");
$stmt->execute([$etudiant_id]);
$projet = $stmt->fetch();

// Vérification : Peut-on déposer ?
if (!$projet || !$projet['encadrant_id']) {
    // Redirection si pas d'encadrant (règle métier)
    header("Location: index.php");
    exit();
}

// 3. TRAITEMENT DU FORMULAIRE
if (isset($_POST['upload_btn'])) {
    
    // Vérification de la case "Originalité"
    if (!isset($_POST['originalite'])) {
        $error = "Vous devez certifier l'originalité de votre travail.";
    } 
    // Vérification du fichier
    elseif (isset($_FILES['rapport']) && $_FILES['rapport']['error'] == 0) {
        
        $resume = trim($_POST['resume']);
        $file = $_FILES['rapport'];
        
        // Contraintes
        $maxSize = 50 * 1024 * 1024; // 50 Mo
        $allowedExt = ['pdf'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $error = "Le fichier est trop volumineux (Max 50 Mo).";
        } elseif (!in_array($fileExt, $allowedExt)) {
            $error = "Format incorrect. Seuls les fichiers PDF sont acceptés.";
        } else {
            // Création du dossier de stockage s'il n'existe pas
            // On le place dans 'public/uploads' pour l'instant (à sécuriser plus tard hors webroot)
            $uploadDir = __DIR__ . '/../../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Nom unique pour éviter les conflits : rapport_IDPROJET_TIMESTAMP.pdf
            $fileName = "rapport_" . $projet['id'] . "_" . time() . ".pdf";
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                
                // Insertion BDD
                $sql = "INSERT INTO rapports (projet_id, nom_fichier, chemin_fichier, taille_fichier, resume, est_original) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmtInsert = $pdo->prepare($sql);
                $stmtInsert->execute([$projet['id'], $file['name'], 'uploads/' . $fileName, $file['size'], $resume]);

                // Mise à jour du statut du projet
                $sqlUpdate = $pdo->prepare("UPDATE projets SET statut = 'rapport_soumis' WHERE id = ?");
                $sqlUpdate->execute([$projet['id']]);

                $message = "Votre rapport a été déposé avec succès !";
            } else {
                $error = "Erreur lors de l'enregistrement du fichier sur le serveur.";
            }
        }
    } else {
        $error = "Veuillez sélectionner un fichier.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Dépôt du Rapport PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light container mt-5">
    
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>

    <div class="card shadow mx-auto" style="max-width: 700px;">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Dépôt du Rapport Final</h3>
        </div>
        <div class="card-body p-4">

            <?php if($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $message ?></div>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            <?php else: ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= $error ?></div>
                <?php endif; ?>

                <p class="text-muted mb-4">
                    Veuillez déposer la version finale de votre rapport. Une fois validé par votre encadrant, vous ne pourrez plus le modifier.
                </p>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Résumé du rapport (Abstract)</label>
                        <textarea name="resume" class="form-control" rows="4" required placeholder="Copiez ici le résumé de votre travail..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fichier PDF (Max 50 Mo)</label>
                        <input type="file" name="rapport" class="form-control" accept=".pdf" required>
                    </div>

                    <div class="form-check bg-light p-3 border rounded mb-4">
                        <input class="form-check-input" type="checkbox" name="originalite" id="checkOrigine" required>
                        <label class="form-check-label text-danger fw-bold" for="checkOrigine">
                            Déclaration d'honneur : Je certifie que ce travail est personnel et original. Je suis conscient que tout plagiat entraînera l'annulation de la soutenance.
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="upload_btn" class="btn btn-success btn-lg">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Soumettre mon Rapport
                        </button>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>