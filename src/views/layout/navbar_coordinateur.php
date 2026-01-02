<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="index.php">
        <i class="fas fa-graduation-cap me-2"></i>Gestion Soutenances
    </a>
    
    <div class="ms-auto text-white d-flex align-items-center">
        <span class="me-3">
            <?php echo isset($_SESSION['user_nom']) ? htmlspecialchars($_SESSION['user_nom']) : 'Coordinateur'; ?>
        </span>
        
        <a href="../../controllers/AuthController.php?logout=1" class="btn btn-sm btn-danger" title="Se déconnecter">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</nav>