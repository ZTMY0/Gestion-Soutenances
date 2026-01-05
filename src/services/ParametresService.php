<?php
function get_param(PDO $pdo, string $key, string $default = ''): string {
    $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = ?");
    $stmt->execute([$key]);
    $v = $stmt->fetchColumn();
    return ($v !== false && $v !== null) ? (string)$v : $default;
}

function set_param(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
    $stmt->execute([$key, $value]);
}
