<?php
session_start();
require_once '../../../config/database.php';

// 1. SÉCURITÉ D'ACCÈS
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

$etudiant_id = $_SESSION['user_id'];
$message = "";
$error = "";

<<<<<<< HEAD
// 2. RÉCUPÉRER LE PROJET
$stmtR = $pdo->prepare("SELECT COUNT(*) FROM rapports WHERE projet_id = ?");
$stmtR->execute([$projet['id']]);
$already = (int)$stmtR->fetchColumn();

if ($already > 0) {
    $error = "Vous avez déjà déposé un rapport. Contactez votre encadrant si vous devez remplacer la version.";
}

=======
// 2. RÉCUPÉRATION DU PROJET
$stmt = $pdo->prepare("SELECT * FROM projets WHERE etudiant_id = ?");
$stmt->execute([$etudiant_id]);
$projet = $stmt->fetch();
>>>>>>> origin/main

if (!$projet || !$projet['encadrant_id']) {
    header("Location: index.php");
    exit();
}

// 3. VÉRIFICATION DOUBLONS
// On vérifie directement dans la table PROJETS maintenant (plus fiable)
$dejaSoumis = !empty($projet['rapport_chemin']);

// 4. TRAITEMENT DE L'UPLOAD
if (isset($_POST['upload_btn'])) {
    
    if ($dejaSoumis) {
        $error = "Un rapport est déjà présent.";
    }
    elseif (!isset($_POST['originalite'])) {
        $error = "Vous devez cocher la case certifiant l'originalité de votre travail.";
    }
    elseif (isset($_FILES['rapport']) && $_FILES['rapport']['error'] == 0) {
        
        $file = $_FILES['rapport'];
        $resume = trim($_POST['resume']);
        // On récupère aussi les nouveaux champs (si tu veux les sauvegarder, il faudra adapter la BDD, mais je les laisse pour ne pas casser le code)
        $mots_cles = $_POST['mots_cles_rapport'] ?? ''; 
        $remerciements = $_POST['remerciements'] ?? '';
        
        $maxSize = 50 * 1024 * 1024; // 50 Mo
        $allowedExt = ['pdf'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
if ($mime !== 'application/pdf') {
    $error = "Le fichier n'est pas un PDF valide.";
}


        // SÉCURITÉ MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if ($file['size'] > $maxSize) {
            $error = "Fichier trop lourd (Max 50 Mo).";
        } elseif (!in_array($fileExt, $allowedExt)) {
            $error = "Extension invalide. Seul le .pdf est autorisé.";
        } elseif ($mime !== 'application/pdf') {
            $error = "Fichier corrompu ou format invalide.";
        } else {
            // UPLOAD
            $uploadDir = __DIR__ . '/../../../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = "rapport_" . $projet['id'] . "_" . time() . ".pdf";
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                
                // --- INSERTION DANS LA TABLE RAPPORTS (Pour l'historique) ---
                $sql = "INSERT INTO rapports (projet_id, nom_fichier, chemin_fichier, taille_fichier, resume, est_original) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmtInsert = $pdo->prepare($sql);
                // Note : On garde 'uploads/' pour la table rapports si tu veux
                $stmtInsert->execute([$projet['id'], $file['name'], 'uploads/' . $fileName, $file['size'], $resume]);

                // --- 🚨 C'EST ICI LA CORRECTION IMPORTANTE 🚨 ---
                // On met à jour la table PROJETS avec le nom du fichier pour l'affichage dashboard
                // On sauvegarde juste le $fileName car index.php ajoute déjà le chemin
                $sqlUpdate = "UPDATE projets SET statut = 'rapport_soumis', rapport_chemin = ? WHERE id = ?";
                $pdo->prepare($sqlUpdate)->execute([$fileName, $projet['id']]);

                $message = "Votre rapport a été déposé avec succès !";
                $dejaSoumis = true; 
            } else {
                $error = "Erreur serveur lors de l'enregistrement du fichier.";
            }
        }
    } else {
        $error = "Veuillez sélectionner un fichier PDF.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépôt Rapport PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .upload-area {
            border: 2px dashed #ced4da;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .upload-area:hover, .upload-area.dragover {
            border-color: #0d6efd;
            background-color: #e9ecef;
            cursor: pointer;
        }
        .card { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .card-header { background: linear-gradient(45deg, #0d6efd, #0a58ca); }
    </style>
</head>
<body class="bg-light py-5">
    
    <div class="container">
        <a href="index.php" class="btn btn-outline-secondary mb-4 rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Tableau de bord
        </a>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card rounded-3 overflow-hidden">
                    <div class="card-header text-white p-4">
                        <h3 class="mb-0 fw-bold"><i class="fas fa-file-pdf me-2"></i>Dépôt du Mémoire Final</h3>
                        <p class="mb-0 opacity-75 small">Projet : <?php echo htmlspecialchars($projet['titre']); ?></p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">

                        <?php if($message): ?>
                            <div class="alert alert-success d-flex align-items-center mb-4">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Bravo !</h5>
                                    <?= $message ?>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="index.php" class="btn btn-primary px-5 rounded-pill">Retour à l'accueil</a>
                            </div>
                        
                        <?php elseif($dejaSoumis): ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-success">
                                    <i class="fas fa-check-circle fa-5x"></i>
                                </div>
                                <h4 class="fw-bold text-dark">Rapport déjà soumis</h4>
                                <p class="text-muted">Votre rapport est en cours d'examen par votre encadrant.</p>
                                <a href="index.php" class="btn btn-outline-primary mt-3 px-4 rounded-pill">Retour</a>
                            </div>

                        <?php else: ?>

                            <?php if($error): ?>
                                <div class="alert alert-danger rounded-3">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">Résumé (Abstract)</label>
                                    <textarea name="resume" class="form-control" rows="4" required placeholder="Copiez ici le résumé de votre travail..."></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">Fichier PDF</label>
                                    <div class="upload-area p-5 text-center position-relative" id="dropArea">
                                        <div id="uploadContent">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h5 class="fw-bold text-dark">Glissez votre rapport ici</h5>
                                            <p class="text-muted small mb-0">ou cliquez pour parcourir (Max 50 Mo)</p>
                                        </div>
                                        <div id="filePreview" class="d-none">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <h5 class="fw-bold text-dark" id="fileName">mon_rapport.pdf</h5>
                                            <p class="text-success small mb-0"><i class="fas fa-check me-1"></i>Fichier prêt à l'envoi</p>
                                        </div>
                                        <input type="file" name="rapport" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" 
                                               accept=".pdf" required onchange="handleFile(this)">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">Mots-clés du rapport</label>
                                    <input type="text" name="mots_cles_rapport" class="form-control" placeholder="Séparés par des virgules" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Remerciements (pour le PV)</label>
                                    <textarea name="remerciements" class="form-control" rows="3" placeholder="Texte court pour les remerciements..."></textarea>
                                </div>

                                <div class="form-check bg-light p-3 border rounded-3 mb-4">
                                    <input class="form-check-input" type="checkbox" name="originalite" id="checkOrigine" required style="transform: scale(1.2); margin-top: 0.3rem;">
                                    <label class="form-check-label ps-2 text-danger fw-bold" for="checkOrigine">
                                        Je certifie sur l'honneur que ce travail est personnel et n'est pas issu de plagiat.
                                    </label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="upload_btn" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                        <i class="fas fa-paper-plane me-2"></i>Soumettre définitivement
                                    </button>
                                </div>
                            </form> 
                            <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropArea = document.getElementById('dropArea');
        const uploadContent = document.getElementById('uploadContent');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                dropArea.classList.remove('dragover');
            });
        });

        function handleFile(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                fileName.textContent = file.name;
                uploadContent.classList.add('d-none');
                filePreview.classList.remove('d-none');
                dropArea.style.borderColor = '#198754';
                dropArea.style.backgroundColor = '#d1e7dd';
            }
        }
    </script>
</body>
</html>
UPDATE projets SET statut = 'rapport_soumis'
