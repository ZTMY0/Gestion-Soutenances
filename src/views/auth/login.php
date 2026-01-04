<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion PFE UEMF</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS externe -->
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body>

    <div class="card login-card">
        <div class="card-header-custom">

            <!-- LOGO UEMF -->
            <img src="../../../public/assets/img/logo_uemf.jpeg"
                 alt="Logo UEMF"
                 class="uemf-logo mb-2">

            <h4 class="fw-bold text-dark mb-1">Espace PFE</h4>
            <p class="text-muted small">Université Euromed de Fès</p>
        </div>

        <div class="login-body">

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center py-2 fs-6 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php
                        if ($_GET['error'] == 'bad_credentials') echo "Identifiant ou mot de passe incorrect.";
                        elseif ($_GET['error'] == 'role_inconnu') echo "Rôle utilisateur non autorisé.";
                        elseif ($_GET['error'] == 'empty') echo "Veuillez remplir tous les champs.";
                        elseif ($_GET['error'] == 'system_error') echo "Erreur système. Réessayez.";
                        else echo "Erreur de connexion.";
                    ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/AuthController.php" method="POST">

                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Identifiant</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text"
                               name="identifiant"
                               class="form-control"
                               required
                               placeholder="Login (ex: abdelmoughit.mossaid)"
                               value="<?php echo isset($_GET['login']) ? htmlspecialchars($_GET['login']) : ''; ?>"
                               autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password"
                               name="password"
                               class="form-control"
                               required
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit"
                            name="login_btn"
                            class="btn btn-primary-uemf shadow-sm">
                        Se connecter <i class="fas fa-arrow-right ms-2"></i>
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
