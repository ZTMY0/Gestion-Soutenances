<?php
session_start();
// SÉCURITÉ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../services/SecurityService.php'; // Audit Trail
$security = new SecurityService($pdo);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

$etudiant_id = $_SESSION['user_id'];
$message = "";
$error = "";

// PROJET & DOUBLONS
$stmt = $pdo->prepare("SELECT * FROM projets WHERE etudiant_id = ?");
$stmt->execute([$etudiant_id]);
$projet = $stmt->fetch();

if (!$projet || empty($projet['encadrant_id'])) {
    header("Location: index.php"); exit();
}

$dejaSoumis = !empty($projet['rapport_chemin']);

// TRAITEMENT UPLOAD
if (isset($_POST['upload_btn'])) {
    if ($dejaSoumis) {
        $error = "Rapport déjà soumis. Contactez votre encadrant pour modification.";
    } elseif (!isset($_POST['originalite'])) {
        $error = "La case 'Originalité' est obligatoire.";
    } elseif (isset($_FILES['rapport']) && $_FILES['rapport']['error'] === 0) {
        
        $file = $_FILES['rapport'];
        $maxSize = 50 * 1024 * 1024; // 50 Mo
        $allowedExt = ['pdf'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if ($file['size'] > $maxSize) {
            $error = "Fichier trop lourd (Max 50 Mo).";
        } elseif (!in_array($fileExt, $allowedExt) || $mime !== 'application/pdf') {
            $error = "Format invalide. PDF uniquement.";
        } else {
            
            $uploadDir = __DIR__ . '/../../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = "rapport_" . $projet['id'] . "_" . time() . ".pdf";
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                try {
                    $pdo->beginTransaction();
                    
                    // 1. Historique Rapports
                    $stmtHist = $pdo->prepare("INSERT INTO rapports (projet_id, nom_fichier, chemin_fichier, taille_fichier, resume, est_original, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                    $stmtHist->execute([$projet['id'], $file['name'], 'uploads/' . $fileName, $file['size'], trim($_POST['resume'])]);

                    // 2. Mise à jour Projet (Lien actif)
                    $sqlUpdate = "UPDATE projets SET statut = 'rapport_soumis', rapport_chemin = ? WHERE id = ?";
                    $pdo->prepare($sqlUpdate)->execute([$fileName, $projet['id']]);

                    // 3. Audit Log
                    $security->logAction($etudiant_id, 'DEPOT_RAPPORT', "Dépôt fichier : $fileName");

                    $pdo->commit();
                    $message = "Votre rapport a été déposé avec succès !";
                    $dejaSoumis = true;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Erreur BDD : " . $e->getMessage();
                    if (file_exists($destPath)) unlink($destPath);
                }
            } else {
                $error = "Erreur technique lors de l'enregistrement.";
            }
        }
    } else {
        $error = "Fichier manquant ou erreur de transfert.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépôt Rapport PFE | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .upload-area { border: 2px dashed #ced4da; border-radius: 10px; transition: all 0.3s ease; background: #f8f9fa; cursor: pointer; }
        .upload-area:hover, .upload-area.dragover { border-color: #0d6efd; background-color: #e9ecef; }
        .card-header { background-color: #004d99; color: white; }
    </style>
</head>
<body class="bg-light py-5">
    <div class="container">
        <a href="index.php" class="btn btn-outline-secondary mb-4 rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card rounded-3 overflow-hidden shadow">
                    <div class="card-header p-4">
                        <h3 class="mb-0 fw-bold"><i class="fas fa-file-pdf me-2"></i>Dépôt du Mémoire Final</h3>
                        <p class="mb-0 opacity-75 small">Projet : <?= htmlspecialchars($projet['titre']) ?></p>
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if($message): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div><h5 class="mb-1">Succès !</h5><?= $message ?></div>
                            </div>
                            <div class="text-center mt-4"><a href="index.php" class="btn btn-primary rounded-pill px-5">Accueil</a></div>
                        
                        <?php elseif($dejaSoumis): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                                <h4>Rapport déjà soumis</h4>
                                <p class="text-muted">En attente de validation.</p>
                                <a href="index.php" class="btn btn-outline-primary mt-3 px-4 rounded-pill">Retour</a>
                            </div>

                        <?php else: ?>
                            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Résumé (Abstract)</label>
                                    <textarea name="resume" class="form-control" rows="4" required placeholder="Résumé du projet..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Mots-clés</label>
                                    <input type="text" name="mots_cles_rapport" class="form-control" placeholder="IA, Web, Sécurité...">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Fichier PDF</label>
                                    <div class="upload-area p-5 text-center position-relative" id="dropArea">
                                        <div id="uploadContent">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h5>Glissez votre rapport ici</h5>
                                            <p class="text-muted small">Max 50 Mo - PDF uniquement</p>
                                        </div>
                                        <div id="filePreview" class="d-none">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <h5 id="fileName">fichier.pdf</h5>
                                        </div>
                                        <input type="file" name="rapport" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" accept=".pdf" required onchange="handleFile(this)">
                                    </div>
                                </div>
                                <div class="form-check mb-4 bg-white p-3 border rounded shadow-sm">
                                    <input class="form-check-input" type="checkbox" name="originalite" required style="transform: scale(1.2); margin-top: 0.3rem;">
                                    <label class="form-check-label ps-2 text-danger fw-bold">Je certifie que ce travail est original.</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="upload_btn" class="btn btn-primary btn-lg rounded-pill shadow-sm">Soumettre définitivement</button>
                                </div>
                            </form> 
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleFile(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileName').textContent = input.files[0].name;
                document.getElementById('uploadContent').classList.add('d-none');
                document.getElementById('filePreview').classList.remove('d-none');
            }
        }
    </script>
</body>
</html>