<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - UEMF</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>

<body class="login-page">

    <div class="card login-card">
        <div class="card-header-custom">
            <img src="../../../public/assets/img/logo_uemf.jpeg" alt="Logo UEMF" class="uemf-logo">
            
            <h5 class="fw-bold text-dark mb-0 mt-2">Espace PFE</h5>
            <p class="text-muted small mb-0">Université Euromed de Fès</p>
        </div>

        <div class="login-body">

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center py-2 fs-6 shadow-sm mb-4 border-0 rounded-1">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php
                        if ($_GET['error'] == 'bad_credentials') echo "Identifiant ou mot de passe incorrect.";
                        elseif ($_GET['error'] == 'role_inconnu') echo "Accès non autorisé.";
                        else echo "Erreur de connexion.";
                    ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/AuthController.php" method="POST">

                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Identifiant</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="identifiant" class="form-control" required placeholder="Login académique" autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="login_btn" class="btn btn-primary-uemf shadow-sm">
                        SE CONNECTER <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>
        </div>

        <div class="card-footer bg-light text-center py-3 border-top small text-muted">
            &copy; 2026 EIDIA - Plateforme de Gestion des Soutenances
        </div>
    </div>

</body>
</html>