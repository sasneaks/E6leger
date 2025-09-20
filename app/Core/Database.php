<?php

/**
 * Classe Database - Gestionnaire de base de données avec PDO
 * Implémente le pattern Singleton pour une connexion unique
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $config;

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/config.php';
        $this->connect();
    }

    /**
     * Récupère l'instance unique de la base de données
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Établit la connexion à la base de données
     */
    private function connect(): void
    {
        $dbConfig = $this->config['database'];
        
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['dbname'],
            $dbConfig['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
        ];

        try {
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        } catch (PDOException $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            throw new Exception('Erreur de connexion à la base de données');
        }
    }

    /**
     * Récupère l'instance PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prépare et exécute une requête SELECT
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Erreur lors de l\'exécution de la requête');
        }
    }

    /**
     * Prépare et exécute une requête SELECT pour un seul résultat
     */
    public function queryOne(string $sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError('QueryOne failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Erreur lors de l\'exécution de la requête');
        }
    }

    /**
     * Exécute une requête INSERT, UPDATE ou DELETE
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError('Execute failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Erreur lors de l\'exécution de la requête');
        }
    }

    /**
     * Exécute une requête INSERT et retourne l'ID inséré
     */
    public function insert(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logError('Insert failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Erreur lors de l\'insertion');
        }
    }

    /**
     * Commence une transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollback();
    }

    /**
     * Vérifie si une transaction est active
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Log les erreurs de base de données
     */
    private function logError(string $message): void
    {
        $logFile = __DIR__ . '/../../storage/logs/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        // Créer le dossier si il n'existe pas
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
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
