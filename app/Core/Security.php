<?php

/**
 * Classe Security - Gestionnaire de sécurité
 * Gestion des tokens CSRF, protection XSS, validation des données
 */
class Security
{
    private static $config;

    /**
     * Initialise la configuration
     */
    private static function init(): void
    {
        if (self::$config === null) {
            self::$config = require_once __DIR__ . '/../../config/config.php';
        }
    }

    /**
     * Génère un token CSRF
     */
    public static function generateCsrfToken(): string
    {
        self::init();
        $session = Session::getInstance();
        
        $token = bin2hex(random_bytes(32));
        $session->set('csrf_token', $token);
        $session->set('csrf_token_time', time());
        
        return $token;
    }

    /**
     * Valide un token CSRF
     */
    public static function validateCsrfToken(string $token): bool
    {
        self::init();
        $session = Session::getInstance();
        
        $storedToken = $session->get('csrf_token');
        $tokenTime = $session->get('csrf_token_time');
        
        // Vérifier si le token existe
        if (!$storedToken || !$tokenTime) {
            return false;
        }
        
        // Vérifier l'expiration (30 minutes)
        if (time() - $tokenTime > 1800) {
            $session->remove('csrf_token');
            $session->remove('csrf_token_time');
            return false;
        }
        
        // Comparer les tokens de façon sécurisée
        return hash_equals($storedToken, $token);
    }

    /**
     * Génère un champ input hidden pour le token CSRF
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        $tokenName = self::$config['security']['csrf_token_name'] ?? '_token';
        
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($tokenName),
            htmlspecialchars($token)
        );
    }

    /**
     * Protection contre les attaques XSS
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Nettoie une chaîne de caractères
     */
    public static function clean(string $string): string
    {
        // Supprime les balises HTML et PHP
        $string = strip_tags($string);
        
        // Supprime les espaces en début/fin
        $string = trim($string);
        
        // Supprime les caractères de contrôle
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        
        return $string;
    }

    /**
     * Valide une adresse email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valide un mot de passe selon des critères de sécurité
     */
    public static function isValidPassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Hash un mot de passe de façon sécurisée
     */
    public static function hashPassword(string $password): string
    {
        self::init();
        $cost = self::$config['security']['password_cost'] ?? 12;
        
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
    }

    /**
     * Vérifie un mot de passe contre son hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Génère un token aléatoire sécurisé
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Valide une URL
     */
    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Protection contre les injections SQL (en complément de PDO)
     */
    public static function sanitizeForSql(string $string): string
    {
        // Cette méthode est un complément - PDO avec requêtes préparées reste prioritaire
        return addslashes(trim($string));
    }

    /**
     * Valide et nettoie un nom d'utilisateur
     */
    public static function validateUsername(string $username): array
    {
        $errors = [];
        $username = trim($username);
        
        if (empty($username)) {
            $errors[] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores';
        }
        
        return [
            'valid' => empty($errors),
            'value' => $username,
            'errors' => $errors
        ];
    }

    /**
     * Génère un nonce pour les CSP (Content Security Policy)
     */
    public static function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }

    /**
     * Protection contre les attaques de timing
     */
    public static function constantTimeEquals(string $known, string $user): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }
        
        // Fallback pour les versions PHP plus anciennes
        if (strlen($known) !== strlen($user)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($known); $i++) {
            $result |= ord($known[$i]) ^ ord($user[$i]);
        }
        
        return $result === 0;
    }

    /**
     * Limite le taux de requêtes (protection contre le brute force)
     */
    public static function rateLimitCheck(string $identifier, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $session = Session::getInstance();
        $key = 'rate_limit_' . md5($identifier);
        
        $attempts = $session->getTemp($key, []);
        $currentTime = time();
        
        // Nettoyer les tentatives anciennes
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) <= $timeWindow;
        });
        
        // Vérifier si la limite est dépassée
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        // Ajouter la tentative actuelle
        $attempts[] = $currentTime;
        $session->setTemp($key, $attempts, $timeWindow);
        
        return true;
    }

    /**
     * Valide un fichier uploadé
     */
    public static function validateUploadedFile(array $file): array
    {
        self::init();
        $errors = [];
        
        // Vérifier s'il y a une erreur d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'Le fichier est trop volumineux';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'Le fichier n\'a été que partiellement téléchargé';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'Aucun fichier n\'a été téléchargé';
                    break;
                default:
                    $errors[] = 'Erreur lors du téléchargement du fichier';
            }
            
            return ['valid' => false, 'errors' => $errors];
        }
        
        $maxSize = self::$config['upload']['max_size'] ?? 5242880; // 5MB par défaut
        $allowedTypes = self::$config['upload']['allowed_types'] ?? ['jpg', 'jpeg', 'png'];
        
        // Vérifier la taille
        if ($file['size'] > $maxSize) {
            $errors[] = 'Le fichier est trop volumineux (' . round($maxSize / 1024 / 1024, 1) . 'MB maximum)';
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'Type de fichier non autorisé. Types acceptés: ' . implode(', ', $allowedTypes);
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'
        ];
        
        if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
            $errors[] = 'Le type de fichier ne correspond pas à son extension';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'extension' => $extension,
            'mime_type' => $mimeType
        ];
    }

    /**
     * Log les tentatives de sécurité suspectes
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        $logFile = __DIR__ . '/../../storage/logs/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logData = [
            'timestamp' => $timestamp,
            'event' => $event,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'context' => $context
        ];
        
        $logMessage = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        // Créer le dossier si il n'existe pas
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
