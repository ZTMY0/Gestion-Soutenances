<?php
session_start();

require_once '../../config/db.php';

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}
$edit_salle = null;

try {
    $stmt = $pdo->query("SELECT * FROM salles ORDER BY nom");
    $salles = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Erreur lors du chargement des salles: " . $e->getMessage();
    $salles = []; // Ensure $salles is an empty array on error
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'add':
                $nom = htmlspecialchars($_POST['nom']);
                $capacite = (int)$_POST['capacite'];
                $equipements = htmlspecialchars($_POST['equipements']);

                $stmt = $pdo->prepare("INSERT INTO salles (nom, capacite, equipements) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $capacite, $equipements]);
                $_SESSION['message'] = 'Salle ajoutée avec succès.';
                break;
            case 'edit':
                $edit_id = (int)$_POST['salle_id'];
                $nom = htmlspecialchars($_POST['nom']);
                $capacite = (int)$_POST['capacite'];
                $equipements = htmlspecialchars($_POST['equipements']);

                $stmt = $pdo->prepare("UPDATE salles SET nom = ?, capacite = ?, equipements = ? WHERE id = ?");
                $stmt->execute([$nom, $capacite, $equipements, $edit_id]);
                $_SESSION['message'] = 'Salle mise à jour avec succès.';
                break;
            case 'delete':
                $delete_id = (int)$_POST['salle_id'];
                $stmt = $pdo->prepare("DELETE FROM salles WHERE id = ?");
                $stmt->execute([$delete_id]);
                $_SESSION['message'] = 'Salle supprimée avec succès.';
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
    }
    header('Location: gestion_salles.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM salles WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_salle = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "Erreur lors du chargement de la salle pour édition: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles et Équipements</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <!-- Assuming a common CSS file for styling -->
    <style>
        /* Specific styles for this page if needed */
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #004d99;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #004d99;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <?php // include '../layout/navbar_assistante.php'; // Assuming an assistante specific navbar ?>
    <div class="container">
        <h1>Gestion des Salles et Équipements</h1>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <h2><?= $edit_salle ? 'Modifier une Salle' : 'Ajouter une Nouvelle Salle' ?></h2>
        <form action="gestion_salles.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_salle ? 'edit' : 'add' ?>">
            <?php if ($edit_salle): ?>
                <input type="hidden" name="salle_id" value="<?= $edit_salle['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="nom">Nom de la Salle:</label>
                <input type="text" id="nom" name="nom" value="<?= $edit_salle ? $edit_salle['nom'] : '' ?>" required>
            </div>
            <div class="form-group">
                <label for="capacite">Capacité:</label>
                <input type="number" id="capacite" name="capacite" value="<?= $edit_salle ? $edit_salle['capacite'] : '' ?>" required min="1">
            </div>
            <div class="form-group">
                <label for="equipements">Équipements (séparés par des virgules):</label>
                <textarea id="equipements" name="equipements"><?= $edit_salle ? $edit_salle['equipements'] : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $edit_salle ? 'Mettre à jour' : 'Ajouter Salle' ?></button>
            <?php if ($edit_salle): ?>
                <a href="gestion_salles.php" class="btn btn-warning">Annuler</a>
            <?php endif; ?>
        </form>

        <h2>Liste des Salles</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Capacité</th>
                    <th>Équipements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salles)): ?>
                    <tr>
                        <td colspan="5">Aucune salle enregistrée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salles as $salle): ?>
                        <tr>
                            <td><?= $salle['id'] ?></td>
                            <td><?= htmlspecialchars($salle['nom']) ?></td>
                            <td><?= htmlspecialchars($salle['capacite']) ?></td>
                            <td><?= htmlspecialchars($salle['equipements']) ?></td>
                            <td>
                                <a href="gestion_salles.php?action=edit&id=<?= $salle['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <form action="gestion_salles.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="salle_id" value="<?= $salle['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?');">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
