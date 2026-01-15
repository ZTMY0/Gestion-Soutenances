<?php
class SecurityService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Enregistre une action sensible dans la base de données
     */
    public function logAction($userId, $action, $details = null) {
        try {
            // Récupération de l'IP de l'utilisateur
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

            $stmt = $this->pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $action, $details, $ip]);
            } catch (PDOException $e) {
                // ON FORCE L'AFFICHAGE DE L'ERREUR A L'ECRAN
               error_log("Erreur Audit Log: " . $e->getMessage());
            }
    }

    /**
     * Génère un jeton CSRF si inexistant
     */
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie le jeton CSRF
     */
    public static function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die(" Alerte Sécurité : Tentative de CSRF bloquée. Veuillez rafraîchir la page.");
        }
    }
}
?>