<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__.'/../config/db_connect.php');
require_once(__DIR__.'/../vendor/autoload.php');

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

function insertUser($data) {
    // Connexion à la base de données
    $pdo = connectDB();
    
    if (!$pdo) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    $gAuth = new GoogleAuthenticator();
    $secret = $gAuth->generateSecret(); // Générer la clé secrète 2FA
    $data['secret'] = $secret;

    // Vérifier si l'utilisateur existe déjà
    if (!checkExistUser($data['email'])) {
        try {
            // Hasher le mot de passe avant de l'insérer en base
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (identifiant, email, mdp, secret) 
                VALUES (:identifiant, :email, :mdp, :secret)
            ");
            $stmt->bindParam(':identifiant', $data['nom'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':mdp', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':secret', $data['secret'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Récupérer l'ID de l'utilisateur qui vient d'être créé
                $userId = $pdo->lastInsertId();

                // Stocker dans la session pour la vérification 2FA
                $_SESSION['temp_user'] = [
                    'id_client'   => $userId,
                    'identifiant' => $data['nom'],
                    'email'       => $data['email'],
                    'secret'      => $data['secret'],
                    'role'        => 'user' // Mettre ici le rôle par défaut, adapte si besoin
                ];

                // Redirection vers la page du QR Code
                header('Location: show_qrcode.php');
                exit;
            } else {
                die("Erreur SQL : " . implode(" - ", $stmt->errorInfo()));
            }
        } catch (PDOException $e) {
            echo "Erreur SQL : " . $e->getMessage();
            return false;
        }
    } else {
        echo "<div class='error-message'>Un utilisateur avec cet email existe déjà.</div>";
        return false;
    }
}

function checkExistUser($email) {
    $pdo = connectDB();
    
    if (!$pdo) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    } catch (PDOException $e) {
        echo "Erreur SQL : " . $e->getMessage();
        return false;
    }
}
?>
