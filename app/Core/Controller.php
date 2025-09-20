<?php

/**
 * Classe Controller - Contrôleur de base
 * Toutes les classes de contrôleurs héritent de cette classe
 */
abstract class Controller
{
    protected $db;
    protected $config;
    protected $session;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = require_once __DIR__ . '/../../config/config.php';
        $this->session = Session::getInstance();
    }

    /**
     * Rend une vue avec des données
     */
    protected function view(string $viewName, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewName) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("Vue {$viewName} non trouvée");
        }

        // Rendre les données disponibles dans la vue
        extract($data, EXTR_SKIP);
        
        // Inclure la vue
        require $viewFile;
    }

    /**
     * Retourne une réponse JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirige vers une URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirige vers la page précédente
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    protected function requireAuth(): void
    {
        if (!$this->session->get('user_id')) {
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        $userRole = $this->session->get('user_role');
        if ($userRole !== $role && $userRole !== 'admin') {
            http_response_code(403);
            $this->view('errors.403');
            exit;
        }
    }

    /**
     * Valide un token CSRF
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_GET['_token'] ?? null;
        
        if (!$token) {
            return false;
        }

        return Security::validateCsrfToken($token);
    }

    /**
     * Valide et nettoie les données d'entrée
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }

    /**
     * Ajoute un message flash
     */
    protected function flash(string $type, string $message): void
    {
        $this->session->flash($type, $message);
    }

    /**
     * Récupère les messages flash
     */
    protected function getFlashes(): array
    {
        return $this->session->getFlashes();
    }

    /**
     * Vérifie si la requête est AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Récupère l'input de la requête
     */
    protected function input(string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }

    /**
     * Upload de fichiers
     */
    protected function uploadFile(array $file, string $directory = 'uploads'): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Vérifications de sécurité
        $maxSize = $this->config['upload']['max_size'];
        $allowedTypes = $this->config['upload']['allowed_types'];
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Fichier trop volumineux');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé');
        }

        // Génération d'un nom unique
        $fileName = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../assets/images/' . $directory . '/';
        
        // Créer le dossier si il n'existe pas
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fullPath = $uploadPath . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return 'assets/images/' . $directory . '/' . $fileName;
        }

        return null;
    }

    /**
     * Log des erreurs
     */
    protected function logError(string $message, array $context = []): void
    {
        $logFile = __DIR__ . '/../../storage/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] ERROR: {$message}{$contextStr}" . PHP_EOL;
        
        // Créer le dossier si il n'existe pas
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Pagination
     */
    protected function paginate(string $sql, array $params = [], int $perPage = null): array
    {
        $perPage = $perPage ?? $this->config['pagination']['per_page'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        // Compter le total
        $countSql = preg_replace('/SELECT .+ FROM/i', 'SELECT COUNT(*) as total FROM', $sql);
        $total = $this->db->queryOne($countSql, $params)['total'];

        // Récupérer les données de la page
        $sql .= " LIMIT {$offset}, {$perPage}";
        $data = $this->db->query($sql, $params);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_previous' => $page > 1,
                'has_next' => $page < ceil($total / $perPage)
            ]
        ];
    }
}
