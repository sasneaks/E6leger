<?php

/**
 * Model User - Gestion des utilisateurs
 */
class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO users (identifiant, email, mdp, role, firstname, lastname, phone, secret) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['identifiant'],
            $data['email'],
            Security::hashPassword($data['password']),
            $data['role'] ?? 'client',
            $data['firstname'] ?? null,
            $data['lastname'] ?? null,
            $data['phone'] ?? null,
            $data['secret'] ?? null
        ]);
    }

    /**
     * Trouve un utilisateur par son ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id_client = ? AND is_active = TRUE";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = TRUE";
        return $this->db->queryOne($sql, [$email]);
    }

    /**
     * Trouve un utilisateur par son identifiant
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE identifiant = ? AND is_active = TRUE";
        return $this->db->queryOne($sql, [$username]);
    }

    /**
     * Authentification d'un utilisateur
     */
    public function authenticate(string $login, string $password): ?array
    {
        // Chercher par email ou identifiant
        $user = $this->findByEmail($login) ?? $this->findByUsername($login);
        
        if ($user && Security::verifyPassword($password, $user['mdp'])) {
            // Mettre à jour la dernière connexion
            $this->updateLastLogin($user['id_client']);
            return $user;
        }
        
        return null;
    }

    /**
     * Met à jour les informations d'un utilisateur
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        $allowedFields = ['identifiant', 'email', 'firstname', 'lastname', 'phone', 'address', 'city', 'zip_code', 'country'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = $field . ' = ?';
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        
        return $this->db->execute($sql, $values);
    }

    /**
     * Change le mot de passe d'un utilisateur
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $sql = "UPDATE users SET mdp = ?, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        return $this->db->execute($sql, [Security::hashPassword($newPassword), $id]);
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id_client != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un identifiant existe déjà
     */
    public function usernameExists(string $username, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE identifiant = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id_client != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Active ou désactive un utilisateur
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE users SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Met à jour la dernière connexion
     */
    private function updateLastLogin(int $id): void
    {
        $sql = "UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        $this->db->execute($sql, [$id]);
    }

    /**
     * Récupère tous les utilisateurs avec pagination
     */
    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id_client, identifiant, email, role, firstname, lastname, 
                       is_active, created_at, last_login_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $users = $this->db->query($sql, [$perPage, $offset]);
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM users";
        $total = $this->db->queryOne($countSql)['total'];
        
        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Récupère les statistiques des utilisateurs
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_month
                FROM users";
        
        return $this->db->queryOne($sql);
    }

    /**
     * Recherche d'utilisateurs
     */
    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT id_client, identifiant, email, role, firstname, lastname, 
                       is_active, created_at, last_login_at 
                FROM users 
                WHERE identifiant LIKE ? OR email LIKE ? OR firstname LIKE ? OR lastname LIKE ?
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $users = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
        
        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM users 
                     WHERE identifiant LIKE ? OR email LIKE ? OR firstname LIKE ? OR lastname LIKE ?";
        $total = $this->db->queryOne($countSql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm])['total'];
        
        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Génère un token de remember me
     */
    public function generateRememberToken(int $userId): string
    {
        $token = Security::generateToken(64);
        
        $sql = "UPDATE users SET remember_token = ?, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        $this->db->execute($sql, [$token, $userId]);
        
        return $token;
    }

    /**
     * Trouve un utilisateur par son token de remember me
     */
    public function findByRememberToken(string $token): ?array
    {
        $sql = "SELECT * FROM users WHERE remember_token = ? AND is_active = TRUE";
        return $this->db->queryOne($sql, [$token]);
    }

    /**
     * Supprime le token de remember me
     */
    public function removeRememberToken(int $userId): bool
    {
        $sql = "UPDATE users SET remember_token = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Configuration 2FA
     */
    public function set2FASecret(int $userId, string $secret): bool
    {
        $sql = "UPDATE users SET secret = ?, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        return $this->db->execute($sql, [$secret, $userId]);
    }

    /**
     * Supprime la configuration 2FA
     */
    public function remove2FA(int $userId): bool
    {
        $sql = "UPDATE users SET secret = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_client = ?";
        return $this->db->execute($sql, [$userId]);
    }
}
