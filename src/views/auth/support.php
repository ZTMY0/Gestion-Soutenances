<?php
session_start();
require_once '../../../config/database.php';

$message = "";
$msg_type = "";

if (isset($_POST['send_ticket'])) {
    $email = htmlspecialchars($_POST['email']);
    $motif = htmlspecialchars($_POST['motif']);
    $msg_content = htmlspecialchars($_POST['message']);

    if (!empty($email) && !empty($msg_content)) {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (email_contact, motif, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $motif, $msg_content])) {
            $message = "Votre demande a été envoyée au coordinateur. Il vous recontactera bientôt sur votre email.";
            $msg_type = "success";
        } else {
            $message = "Erreur technique.";
            $msg_type = "danger";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $msg_type = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Support - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="fas fa-life-ring me-2"></i>Contacter le Coordinateur</h5>
        </div>
        <div class="card-body p-4">
            
            <?php if($message): ?>
                <div class="alert alert-<?= $msg_type ?>"><?= $message ?></div>
                <div class="text-center">
                    <a href="login.php" class="btn btn-outline-dark btn-sm">Retour à la connexion</a>
                </div>
            <?php else: ?>

                <p class="text-muted small mb-4">
                    Vous n'arrivez pas à vous connecter ? Remplissez ce formulaire pour alerter l'administration.
                </p>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Votre Email (pour la réponse)</label>
                        <input type="email" name="email" class="form-control" placeholder="ex: mon.email.perso@gmail.com" required>
                    </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Motif</label>
                    <select name="motif" class="form-select">
                        <optgroup label="Étudiant">
                            <option value="Etudiant - MDP oublié">Mot de passe oublié</option>
                            <option value="Etudiant - Bloqué">Compte bloqué</option>
                        </optgroup>
                        <optgroup label="Enseignant / Staff">
                            <option value="Prof - Accès perdu">Accès perdu</option>
                            <option value="Prof - 2FA">Problème Double Authentification</option>
                        </optgroup>
                        <option value="Autre">Autre demande</option>
                    </select>
                </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Message</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Expliquez votre problème..." required></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="send_ticket" class="btn btn-primary-uemf fw-bold">
                            ENVOYER LA DEMANDE <i class="fas fa-paper-plane ms-2"></i>
                        </button>
                        <a href="login.php" class="btn btn-light btn-sm text-muted">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>