<?php
// src/views/auth/logout.php

session_start(); // On récupère la session active
session_unset(); // On vide les variables
session_destroy(); // On détruit la session complètement

// Redirection vers la page de login (qui est dans le même dossier)
header("Location: login.php");
exit();
?>