<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - UEMF PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 10px;
        }
        .uemf-header {
            color: #0d6efd; /* Tu peux mettre ici le code couleur exact de l'UEMF */
        }
    </style>
</head>
<body>

    <div class="card shadow-lg login-card p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold uemf-header mt-2">UEMF</h3>
            <p class="text-muted small">Portail de Gestion des PFE</p>
        </div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center py-2 fs-6">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                if($_GET['error'] == 'bad_credentials') echo "Identifiant ou mot de passe incorrect.";
                elseif($_GET['error'] == 'role_inconnu') echo "Compte non autorisé.";
                elseif($_GET['error'] == 'empty') echo "Veuillez remplir tous les champs.";
                else echo "Erreur de connexion.";
                ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/AuthController.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">Identifiant académique</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="login" class="form-control border-start-0" required 
                           placeholder="Ex: prenom.nom" 
                           value="<?php echo isset($_GET['login']) ? htmlspecialchars($_GET['login']) : ''; ?>"
                           autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-secondary">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" name="login_btn" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                Se connecter
            </button>
        </form>
        
        <div class="mt-4 text-center border-top pt-3">
            <small class="text-muted" style="font-size: 0.8rem;">
                © 2026 Université Euromed de Fès<br>
                Service Informatique - EIDIA
            </small>
        </div>
    </div>

</body>
</html>