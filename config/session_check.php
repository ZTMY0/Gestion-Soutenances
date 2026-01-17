<?php
// Gestion-Soutenances/config/session_check.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has a role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    // Redirect to login page if not authenticated
    header("Location: ../auth/login.php");
    exit();
}

// Optionally, you can add more specific role-based checks here
// For example, if generer_pdf.php is only for 'assistante' role:
// if ($_SESSION['user_role'] !== 'assistante') {
//     header("Location: ../../index.php"); // Redirect to a more general page or error page
//     exit();
// }

?>