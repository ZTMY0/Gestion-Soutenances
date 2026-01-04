<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>Gestion Soutenances
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-chart-line me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="projets.php">
                        <i class="fas fa-list me-1"></i>Projets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold text-warning" href="affectation.php">
                        <i class="fas fa-magic me-1"></i>Affectation IA
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="voir_jury.php">
                        <i class="fas fa-users me-1"></i>Jurys
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center text-white">
                <span class="me-3 d-none d-lg-block">
                    <i class="fas fa-user-tie me-2"></i>
                    <?php echo isset($_SESSION['user_nom']) ? htmlspecialchars($_SESSION['user_nom']) : 'Coordinateur'; ?>
                </span>
                
                <a href="../../controllers/AuthController.php?logout=1" class="btn btn-danger btn-sm shadow-sm" title="Se déconnecter">
                    <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav>