<?php
session_start();
require_once '../../../config/session_check.php';
require_once '../../../config/db.php';

// Check if the user is an assistante, if not, redirect
if ($_SESSION['user_role'] !== 'assistante') {
    header("Location: ../../auth/login.php"); // Redirect to login or an unauthorized page
    exit();
}

$message = '';
$soutenances = []; // To store a list of soutenances for selection

try {
    // Fetch a list of soutenances to associate the archive with
    // For simplicity, let's fetch some basic soutenance info.
    // In a real scenario, you might fetch only those awaiting archiving.
    $stmt = $pdo->query("SELECT s.id, p.titre FROM soutenances s JOIN projets p ON s.projet_id = p.id ORDER BY p.titre");
    $soutenances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors du chargement des soutenances: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    $target_dir = __DIR__ . "/../../../public/archives/";
    $soutenance_id = $_POST['soutenance_id'] ?? null;

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_file_name = basename($_FILES["document_file"]["name"]);
    $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $new_file_name = uniqid('archive_') . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;
    $uploadOk = 1;

    // Check if file already exists (unlikely with uniqid, but good practice)
    if (file_exists($target_file)) {
        $message = "Désolé, le fichier existe déjà.";
        $uploadOk = 0;
    }

    // Check file size (e.g., max 5MB)
    if ($_FILES["document_file"]["size"] > 5000000) {
        $message = "Désolé, votre fichier est trop grand (max 5MB).";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($file_extension != "pdf" && $file_extension != "jpg" && $file_extension != "png" && $file_extension != "doc" && $file_extension != "docx") {
        $message = "Désolé, seuls les fichiers PDF, JPG, PNG, DOC, DOCX sont autorisés.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $_SESSION['message'] = $message;
    } else {
        if (move_uploaded_file($_FILES["document_file"]["tmp_name"], $target_file)) {
            // Save metadata to database (this part needs a table and implementation)
            // For now, just a success message
            $_SESSION['message'] = "Le fichier ". htmlspecialchars($original_file_name). " a été archivé avec succès.";
            
            // Here you would typically insert a record into an 'archives' table:
            // $stmt = $pdo->prepare("INSERT INTO archives (soutenance_id, file_path, original_name, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
            // $stmt->execute([$soutenance_id, 'public/archives/' . $new_file_name, $original_file_name, $_SESSION['user_id']]);

        } else {
            $_SESSION['message'] = "Désolé, une erreur s'est produite lors de l'archivage de votre fichier.";
        }
    }
    header('Location: archiver_soutenance.php');
    exit();
}

// Handle messages from redirection
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archiver une Soutenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">LOGISTIQUE PFE</a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Assistante Administrative</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-danger">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Archiver une Soutenance</h1>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'succès') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm p-4">
            <form action="archiver_soutenance.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="soutenance_id" class="form-label">Sélectionner la Soutenance:</label>
                    <select class="form-select" id="soutenance_id" name="soutenance_id" required>
                        <option value="">-- Choisir une soutenance --</option>
                        <?php foreach ($soutenances as $soutenance): ?>
                            <option value="<?= htmlspecialchars($soutenance['id']) ?>">
                                Soutenance <?= htmlspecialchars($soutenance['titre']) ?> (ID: <?= htmlspecialchars($soutenance['id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="document_file" class="form-label">Sélectionner le document à archiver (PDF, JPG, PNG, DOC, DOCX - max 5MB):</label>
                    <input class="form-control" type="file" id="document_file" name="document_file" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Archiver le document
                </button>
            </form>
        </div>
    </div>
</body>
</html>
