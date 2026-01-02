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
$stmt = $pdo->query("SELECT * FROM filieres ORDER BY nom");
$filieres = $stmt->fetchAll();

if (isset($_POST['import_csv'])) {
    if (isset($_FILES['fichier_csv']) && $_FILES['fichier_csv']['error'] == 0) {
        $target_filiere_id = $_POST['filiere_id']; 
        
        // --- NOUVELLE LOGIQUE DE NETTOYAGE ---
        if (isset($_POST['reinitialiser']) && $_POST['reinitialiser'] == '1') {
            try {
                // 1. D'abord, on supprime les PROJETS liés aux étudiants de cette filière
                // Cela libère les étudiants pour la suppression
                $sqlDeleteProjets = "DELETE p FROM projets p 
                                     INNER JOIN users u ON p.etudiant_id = u.id 
                                     WHERE u.role = 'etudiant' AND u.filiere_id = ?";
                $stmtDel = $pdo->prepare($sqlDeleteProjets);
                $stmtDel->execute([$target_filiere_id]);

                // 2. Maintenant on peut supprimer les ÉTUDIANTS sans erreur
                $sqlDeleteUsers = "DELETE FROM users WHERE role = 'etudiant' AND filiere_id = ?";
                $stmtDelUsers = $pdo->prepare($sqlDeleteUsers);
                $stmtDelUsers->execute([$target_filiere_id]);
                
            } catch (PDOException $e) {
                $message = "<div class='alert alert-danger'>Erreur de nettoyage : " . $e->getMessage() . "</div>";
            }
        }
        // -------------------------------------

        $csvFile = fopen($_FILES['fichier_csv']['tmp_name'], 'r');
        
        $bom = fread($csvFile, 3);
        if ($bom != "\xEF\xBB\xBF") rewind($csvFile);

        $count = 0;
        while (($line = fgetcsv($csvFile, 1000, ";")) !== FALSE) {
            if (count($line) < 4) {
                rewind($csvFile);
                while (($line = fgetcsv($csvFile, 1000, ",")) !== FALSE) {
                   processLine($line, $pdo, $target_filiere_id, $count);
                }
                break;
            }
            processLine($line, $pdo, $target_filiere_id, $count);
        }
        fclose($csvFile);
        if(empty($message)) { 
            $message = "<div class='alert alert-success'>✅ $count étudiants importés (Liste mise à jour).</div>";
        }
    }
}

function processLine($line, $pdo, $filiere_id, &$count) {
    if(count($line) < 4) return;
    $nom = trim($line[0]);
    $email = trim($line[1]);
    $login = trim($line[2]);
    $mdp = trim($line[3]);
    if (empty($nom) || empty($email)) return;

    $sql = "INSERT INTO users (nom, email, login, password, role, filiere_id) 
            VALUES (?, ?, ?, ?, 'etudiant', ?)
            ON DUPLICATE KEY UPDATE nom=VALUES(nom), login=VALUES(login), password=VALUES(password)";
    $pdo->prepare($sql)->execute([$nom, $email, $login, password_hash($mdp, PASSWORD_DEFAULT), $filiere_id]);
    $count++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Importation Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <?php include __DIR__ . '/../layout/navbar_coordinateur.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary"><i class="fas fa-user-grad me-2"></i>Importation Étudiants</h3>
            <a href="index.php" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Retour Dashboard
            </a>
        </div>
        
        <div class="card shadow border-0 p-4 mx-auto" style="max-width: 600px;">
            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">1. Filière de destination</label>
                    <select name="filiere_id" class="form-select" required>
                        <?php foreach($filieres as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">2. Fichier CSV</label>
                    <input type="file" name="fichier_csv" class="form-control" accept=".csv" required>
                </div>

                <div class="form-check mb-4 p-3 bg-light rounded border">
                    <input class="form-check-input" type="checkbox" name="reinitialiser" value="1" id="resetCheck">
                    <label class="form-check-label text-danger fw-bold" for="resetCheck">
                        <i class="fas fa-trash-alt me-1"></i> Vider la filière avant l'import
                    </label>
                    <div class="form-text small">Supprime aussi les projets liés à cette filière !</div>
                </div>

                <button type="submit" name="import_csv" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                    <i class="fas fa-upload me-2"></i>Lancer l'importation
                </button>
            </form>
        </div>
    </div>
</body>
</html>