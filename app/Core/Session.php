<?php

/**
 * Classe Session - Gestionnaire de session sécurisé
 * Implémente le pattern Singleton
 */
class Session
{
    private static $instance = null;
    private $config;

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/config.php';
        $this->start();
    }

    /**
     * Récupère l'instance unique de la session
     */
    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Démarre la session de façon sécurisée
     */
    private function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration sécurisée de la session
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Nom personnalisé pour la session
            session_name($this->config['security']['session_name']);
            
            session_start();
            
            // Régénération périodique de l'ID de session
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerateId();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
                $this->regenerateId();
            }
        }
    }

    /**
     * Régénère l'ID de session
     */
    public function regenerateId(): void
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Définit une valeur en session
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de session
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe en session
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une valeur de session
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Vide complètement la session
     */
    public function clear(): void
    {
        session_unset();
    }

    /**
     * Détruit la session
     */
    public function destroy(): void
    {
        session_destroy();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    /**
     * Ajoute un message flash
     */
    public function flash(string $type, string $message): void
    {
        $_SESSION['flash_messages'][$type][] = $message;
    }

    /**
     * Récupère et supprime les messages flash
     */
    public function getFlashes(): array
    {
        $flashes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $flashes;
    }

    /**
     * Récupère les messages flash d'un type spécifique
     */
    public function getFlash(string $type): array
    {
        $flashes = $_SESSION['flash_messages'][$type] ?? [];
        unset($_SESSION['flash_messages'][$type]);
        return $flashes;
    }

    /**
     * Connecte un utilisateur
     */
    public function login(array $user): void
    {
        $this->regenerateId();
        
        $_SESSION['user_id'] = $user['id_client'];
        $_SESSION['user_username'] = $user['identifiant'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['is_logged_in'] = true;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        $this->clear();
        $this->destroy();
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    public function isLoggedIn(): bool
    {
        return $this->get('is_logged_in', false);
    }

    /**
     * Récupère les informations de l'utilisateur connecté
     */
    public function getUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $this->get('user_id'),
            'username' => $this->get('user_username'),
            'email' => $this->get('user_email'),
            'role' => $this->get('user_role'),
            'login_time' => $this->get('login_time')
        ];
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        $userRole = $this->get('user_role');
        return $userRole === $role || $userRole === 'admin';
    }

    /**
     * Stocke des données temporaires (panier, formulaire en cours, etc.)
     */
    public function setTemp(string $key, $value, int $duration = 3600): void
    {
        $_SESSION['temp_data'][$key] = [
            'value' => $value,
            'expires' => time() + $duration
        ];
    }

    /**
     * Récupère des données temporaires
     */
    public function getTemp(string $key, $default = null)
    {
        if (!isset($_SESSION['temp_data'][$key])) {
            return $default;
        }

        $tempData = $_SESSION['temp_data'][$key];
        
        // Vérifier si les données ont expiré
        if (time() > $tempData['expires']) {
            unset($_SESSION['temp_data'][$key]);
            return $default;
        }

        return $tempData['value'];
    }

    /**
     * Nettoie les données temporaires expirées
     */
    public function cleanExpiredTemp(): void
    {
        if (!isset($_SESSION['temp_data'])) {
            return;
        }

        $currentTime = time();
        foreach ($_SESSION['temp_data'] as $key => $data) {
            if ($currentTime > $data['expires']) {
                unset($_SESSION['temp_data'][$key]);
            }
        }
    }

    /**
     * Récupère l'ID de session actuel
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Empêche le clonage (Singleton)
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation (Singleton)
     */
    public function __wakeup() {}
}
