<?php

/**
 * Loader pour les variables d'environnement
 * Charge les variables depuis .env si le fichier existe
 */

function loadEnv($filePath = null) {
    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env';
    }
    
    if (!file_exists($filePath)) {
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Séparer la clé et la valeur
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Supprimer les guillemets si présents
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            // Définir la variable d'environnement
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
    
    return true;
}

/**
 * Récupère une variable d'environnement avec une valeur par défaut
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Conversion des valeurs booléennes
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }
    
    return $value;
}

// Charger automatiquement le fichier .env
loadEnv();
