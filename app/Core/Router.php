<?php

/**
 * Classe Router - Gestionnaire de routes simple et efficace
 */
class Router
{
    private $routes = [];
    private $namedRoutes = [];
    private $config;

    public function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/config.php';
    }

    /**
     * Ajoute une route GET
     */
    public function get(string $path, $handler, string $name = null): void
    {
        $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * Ajoute une route POST
     */
    public function post(string $path, $handler, string $name = null): void
    {
        $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * Ajoute une route PUT
     */
    public function put(string $path, $handler, string $name = null): void
    {
        $this->addRoute('PUT', $path, $handler, $name);
    }

    /**
     * Ajoute une route DELETE
     */
    public function delete(string $path, $handler, string $name = null): void
    {
        $this->addRoute('DELETE', $path, $handler, $name);
    }

    /**
     * Ajoute une route au tableau des routes
     */
    private function addRoute(string $method, string $path, $handler, string $name = null): void
    {
        $route = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'handler' => $handler,
            'params' => []
        ];

        $this->routes[] = $route;

        // Stocker les routes nommées
        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Normalise le chemin de la route
     */
    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    /**
     * Résout la route actuelle
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getRequestUri();

        // Gestion des méthodes HTTP spoofées (via _method)
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $requestMethod, $requestUri)) {
                $this->executeRoute($route);
                return;
            }
        }

        // Aucune route trouvée - 404
        $this->handle404();
    }

    /**
     * Récupère l'URI de la requête
     */
    private function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Supprimer les paramètres GET
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Supprimer le préfixe du dossier si nécessaire
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $uri = substr($uri, strlen($scriptName));
        }

        return $this->normalizePath($uri);
    }

    /**
     * Vérifie si une route correspond à la requête
     */
    private function matchRoute(array &$route, string $method, string $uri): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }

        // Route exacte
        if ($route['path'] === $uri) {
            return true;
        }

        // Route avec paramètres
        $pattern = $this->buildPattern($route['path']);
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Supprimer la correspondance complète
            $route['params'] = $matches;
            return true;
        }

        return false;
    }

    /**
     * Construit un pattern regex pour les routes avec paramètres
     */
    private function buildPattern(string $path): string
    {
        // Remplacer {param} par un groupe de capture
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Exécute la route correspondante
     */
    private function executeRoute(array $route): void
    {
        $handler = $route['handler'];
        $params = $route['params'];

        if (is_string($handler)) {
            // Format "Controller@method"
            if (strpos($handler, '@') !== false) {
                [$controllerName, $method] = explode('@', $handler);
                $this->executeController($controllerName, $method, $params);
            } else {
                // Inclusion de fichier
                $this->includeFile($handler, $params);
            }
        } elseif (is_callable($handler)) {
            // Fonction anonyme
            call_user_func_array($handler, $params);
        }
    }

    /**
     * Exécute une méthode de contrôleur
     */
    private function executeController(string $controllerName, string $method, array $params = []): void
    {
        $controllerClass = $controllerName . 'Controller';
        $controllerFile = __DIR__ . '/../Controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            throw new Exception("Contrôleur {$controllerClass} non trouvé");
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            throw new Exception("Classe {$controllerClass} non trouvée");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new Exception("Méthode {$method} non trouvée dans {$controllerClass}");
        }

        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Inclut un fichier PHP
     */
    private function includeFile(string $file, array $params = []): void
    {
        $filePath = __DIR__ . '/../../' . ltrim($file, '/');
        
        if (!file_exists($filePath)) {
            throw new Exception("Fichier {$file} non trouvé");
        }

        // Rendre les paramètres disponibles dans le fichier inclus
        extract($params, EXTR_SKIP);
        
        require $filePath;
    }

    /**
     * Génère une URL pour une route nommée
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route nommée '{$name}' non trouvée");
        }

        $route = $this->namedRoutes[$name];
        $path = $route['path'];

        // Remplacer les paramètres dans l'URL
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return $this->config['app']['url'] . $path;
    }

    /**
     * Gère les erreurs 404
     */
    private function handle404(): void
    {
        http_response_code(404);
        
        $errorFile = __DIR__ . '/../Views/errors/404.php';
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo '<h1>404 - Page non trouvée</h1>';
        }
        exit;
    }

    /**
     * Redirection
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Redirection vers une route nommée
     */
    public function redirectTo(string $name, array $params = [], int $statusCode = 302): void
    {
        $url = $this->url($name, $params);
        $this->redirect($url, $statusCode);
    }
}
