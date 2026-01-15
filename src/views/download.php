<?php
// src/views/download.php
session_start();
// Utilisez __DIR__ pour remonter à la config (ajustez les ../ selon l'emplacement exact)
require_once __DIR__ . '/../../config/database.php';

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Accès interdit.");
}

// 2. Récupérer le nom du fichier
$file = basename($_GET['f'] ?? ''); // basename empêche le piratage de chemin (../)

// 3. Définir le chemin réel (Dossier uploads)
$filepath = __DIR__ . '/../../public/uploads/' . $file;

if (!$file || !file_exists($filepath)) {
    die("Fichier introuvable.");
}

// 4. VERIFICATION DES DROITS (RBAC)
$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$autorise = false;

// Cas Étudiant : Il ne peut voir QUE son propre rapport
if ($role === 'etudiant') {
    // On vérifie en base si ce fichier appartient bien à cet étudiant
    $stmt = $pdo->prepare("SELECT id FROM projets WHERE etudiant_id = ? AND rapport_chemin LIKE ?");
    $stmt->execute([$user_id, "%$file%"]);
    if ($stmt->fetch()) {
        $autorise = true;
    }
}
// Cas Prof : Il peut voir les projets qu'il encadre
elseif ($role === 'prof') {
    $stmt = $pdo->prepare("SELECT id FROM projets WHERE encadrant_id = ? AND rapport_chemin LIKE ?");
    $stmt->execute([$user_id, "%$file%"]);
    if ($stmt->fetch()) $autorise = true;
}
// Cas Coordinateur / Directeur : Accès total
elseif ($role === 'coordinateur' || $role === 'directeur') {
    $autorise = true;
}

if (!$autorise) {
    die(" Vous n'avez pas la permission de télécharger ce document.");
}

// 5. Livrer le fichier
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $file . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>