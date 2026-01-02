<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

if (isset($_POST['import_profs'])) {
    if (isset($_FILES['fichier_csv']) && $_FILES['fichier_csv']['error'] == 0) {
        
        // --- CORRECTIF : Force le nettoyage même avec des clés étrangères ---
        if (isset($_POST['reinitialiser']) && $_POST['reinitialiser'] == '1') {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->query("DELETE FROM users WHERE role = 'prof'");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        $file = fopen($_FILES['fichier_csv']['tmp_name'], 'r');
        $count = 0;
        
        while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
            // On tente aussi la virgule si le point-virgule échoue
            if (count($line) < 6) {
                $line = str_getcsv($line[0], ","); 
            }
            if(count($line) < 6) continue;

            $nom = trim($line[0]);
            $email = trim($line[1]);
            $login = trim($line[2]);
            $f_code = trim($line[3]);
            $specs = trim($line[4]);
            $mdp = trim($line[5]);

            $stmtF = $pdo->prepare("SELECT id FROM filieres WHERE code = ?");
            $stmtF->execute([$f_code]);
            $f_id = ($f = $stmtF->fetch()) ? $f['id'] : NULL;

            $sql = "INSERT INTO users (nom, email, login, password, role, filiere_id, specialite) 
                    VALUES (?, ?, ?, ?, 'prof', ?, ?)
                    ON DUPLICATE KEY UPDATE nom=VALUES(nom), login=VALUES(login), specialite=VALUES(specialite)";
            $pdo->prepare($sql)->execute([$nom, $email, $login, password_hash($mdp, PASSWORD_DEFAULT), $f_id, $specs]);
            $count++;
        }
        fclose($file);
        $message = "<div class='alert alert-warning text-dark'>✅ $count professeurs traités.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Importation Professeurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../layout/navbar_coordinateur.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-warning text-dark"><i class="fas fa-chalkboard-teacher me-2"></i>Importation Professeurs</h3>
            <a href="index.php" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Retour Dashboard
            </a>
        </div>

        <div class="card shadow border-0 p-4 mx-auto" style="max-width: 600px;">
            <?php echo $message; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Fichier CSV (Profs)</label>
                    <input type="file" name="fichier_csv" class="form-control" accept=".csv" required>
                </div>

                <div class="form-check mb-4 p-3 bg-light rounded border">
                    <input class="form-check-input" type="checkbox" name="reinitialiser" value="1" id="resetProf">
                    <label class="form-check-label text-danger fw-bold" for="resetProf">
                         <i class="fas fa-trash-alt me-1"></i> Vider la liste des profs avant l'import
                    </label>
                </div>

                <button type="submit" name="import_profs" class="btn btn-warning w-100 py-2 fw-bold shadow-sm">
                    <i class="fas fa-upload me-2"></i>Importer les Profs
                </button>
            </form>
        </div>
    </div>
</body>
</html>