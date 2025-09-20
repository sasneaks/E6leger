<?php

/**
 * Classe Validator - Système de validation des données
 */
class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Valide les données selon les règles fournies
     */
    public function validate(array $data, array $rules): array
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }

        return [
            'valid' => empty($this->errors),
            'data' => $this->data,
            'errors' => $this->errors
        ];
    }

    /**
     * Valide un champ selon ses règles
     */
    private function validateField(string $field, string $rules): void
    {
        $value = $this->data[$field] ?? null;
        $rulesArray = explode('|', $rules);

        foreach ($rulesArray as $rule) {
            $this->applyRule($field, $value, $rule);
        }
    }

    /**
     * Applique une règle de validation
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                $this->validateRequired($field, $value);
                break;
            
            case 'email':
                $this->validateEmail($field, $value);
                break;
            
            case 'min':
                $this->validateMin($field, $value, (int)$parameter);
                break;
            
            case 'max':
                $this->validateMax($field, $value, (int)$parameter);
                break;
            
            case 'numeric':
                $this->validateNumeric($field, $value);
                break;
            
            case 'integer':
                $this->validateInteger($field, $value);
                break;
            
            case 'url':
                $this->validateUrl($field, $value);
                break;
            
            case 'unique':
                $this->validateUnique($field, $value, $parameter);
                break;
            
            case 'exists':
                $this->validateExists($field, $value, $parameter);
                break;
            
            case 'confirmed':
                $this->validateConfirmed($field, $value);
                break;
            
            case 'alpha':
                $this->validateAlpha($field, $value);
                break;
            
            case 'alpha_num':
                $this->validateAlphaNum($field, $value);
                break;
            
            case 'regex':
                $this->validateRegex($field, $value, $parameter);
                break;
            
            case 'in':
                $this->validateIn($field, $value, $parameter);
                break;
            
            case 'date':
                $this->validateDate($field, $value);
                break;
            
            case 'password':
                $this->validatePassword($field, $value);
                break;
        }
    }

    /**
     * Valide que le champ est requis
     */
    private function validateRequired(string $field, $value): void
    {
        if (empty($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' est requis');
        }
    }

    /**
     * Valide une adresse email
     */
    private function validateEmail(string $field, $value): void
    {
        if (!empty($value) && !Security::isValidEmail($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être une adresse email valide');
        }
    }

    /**
     * Valide la longueur minimale
     */
    private function validateMin(string $field, $value, int $min): void
    {
        if (!empty($value) && strlen($value) < $min) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit contenir au moins ' . $min . ' caractères');
        }
    }

    /**
     * Valide la longueur maximale
     */
    private function validateMax(string $field, $value, int $max): void
    {
        if (!empty($value) && strlen($value) > $max) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' ne peut pas dépasser ' . $max . ' caractères');
        }
    }

    /**
     * Valide qu'une valeur est numérique
     */
    private function validateNumeric(string $field, $value): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être numérique');
        }
    }

    /**
     * Valide qu'une valeur est un entier
     */
    private function validateInteger(string $field, $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être un nombre entier');
        }
    }

    /**
     * Valide une URL
     */
    private function validateUrl(string $field, $value): void
    {
        if (!empty($value) && !Security::isValidUrl($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être une URL valide');
        }
    }

    /**
     * Valide l'unicité en base de données
     */
    private function validateUnique(string $field, $value, string $table): void
    {
        if (empty($value)) return;

        $db = Database::getInstance();
        $result = $db->queryOne("SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?", [$value]);
        
        if ($result['count'] > 0) {
            $this->addError($field, 'Cette valeur pour ' . $this->getFieldName($field) . ' existe déjà');
        }
    }

    /**
     * Valide l'existence en base de données
     */
    private function validateExists(string $field, $value, string $table): void
    {
        if (empty($value)) return;

        $db = Database::getInstance();
        $result = $db->queryOne("SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?", [$value]);
        
        if ($result['count'] == 0) {
            $this->addError($field, 'Cette valeur pour ' . $this->getFieldName($field) . ' n\'existe pas');
        }
    }

    /**
     * Valide la confirmation d'un champ (ex: password_confirmation)
     */
    private function validateConfirmed(string $field, $value): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;

        if ($value !== $confirmValue) {
            $this->addError($field, 'La confirmation ne correspond pas');
        }
    }

    /**
     * Valide que la valeur ne contient que des lettres
     */
    private function validateAlpha(string $field, $value): void
    {
        if (!empty($value) && !ctype_alpha($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' ne doit contenir que des lettres');
        }
    }

    /**
     * Valide que la valeur ne contient que des lettres et des chiffres
     */
    private function validateAlphaNum(string $field, $value): void
    {
        if (!empty($value) && !ctype_alnum($value)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' ne doit contenir que des lettres et des chiffres');
        }
    }

    /**
     * Valide avec une expression régulière
     */
    private function validateRegex(string $field, $value, string $pattern): void
    {
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, 'Le format du champ ' . $this->getFieldName($field) . ' n\'est pas valide');
        }
    }

    /**
     * Valide que la valeur est dans une liste
     */
    private function validateIn(string $field, $value, string $list): void
    {
        $allowedValues = explode(',', $list);
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être une des valeurs suivantes: ' . $list);
        }
    }

    /**
     * Valide une date
     */
    private function validateDate(string $field, $value): void
    {
        if (!empty($value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->addError($field, 'Le champ ' . $this->getFieldName($field) . ' doit être une date valide (YYYY-MM-DD)');
            }
        }
    }

    /**
     * Valide un mot de passe selon les critères de sécurité
     */
    private function validatePassword(string $field, $value): void
    {
        if (!empty($value)) {
            $result = Security::isValidPassword($value);
            if (!$result['valid']) {
                foreach ($result['errors'] as $error) {
                    $this->addError($field, $error);
                }
            }
        }
    }

    /**
     * Ajoute une erreur
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Récupère le nom lisible d'un champ
     */
    private function getFieldName(string $field): string
    {
        $names = [
            'username' => 'nom d\'utilisateur',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'name' => 'nom',
            'firstname' => 'prénom',
            'lastname' => 'nom de famille',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'city' => 'ville',
            'zip' => 'code postal',
            'country' => 'pays',
            'quantity' => 'quantité',
            'price' => 'prix',
            'title' => 'titre',
            'description' => 'description',
            'category' => 'catégorie'
        ];

        return $names[$field] ?? $field;
    }

    /**
     * Méthodes statiques pour validation rapide
     */
    
    /**
     * Valide rapidement un email
     */
    public static function email(string $email): bool
    {
        return Security::isValidEmail($email);
    }

    /**
     * Valide rapidement un mot de passe
     */
    public static function password(string $password): array
    {
        return Security::isValidPassword($password);
    }

    /**
     * Nettoie et valide une chaîne
     */
    public static function cleanString(string $string): string
    {
        return Security::clean($string);
    }

    /**
     * Échappe pour l'affichage HTML
     */
    public static function escape(string $string): string
    {
        return Security::escape($string);
    }
}
