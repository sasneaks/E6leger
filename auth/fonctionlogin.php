<?php


require_once(__DIR__.'/../config/db_connect.php');


function login($data) {
    // Obtenir la connexion à la base de données
    $pdo = connectDB();

    if (!$pdo) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    try {
        // Requête pour récupérer l'utilisateur par email
        $stmt = $pdo->prepare('SELECT id_client, identifiant, email, mdp, secret, role FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'utilisateur existe
        if ($user) {
            // Vérifier le mot de passe (le mot de passe est haché dans la base de données)
            if (password_verify($data['password'], $user['mdp'])) {
                // Stocker les informations utilisateur dans la session
                $_SESSION['temp_user'] = [
                    'id_client' => $user['id_client'],
                    'identifiant' => $user['identifiant'],
                    'email' => $user['email'],
                    'secret' => $user['secret'],
                    'role' => $user['role']
                ];

                header('Location: verify_2fa.php');
                return true; // Connexion réussie
            } else {
                echo "<div class='error-message'>Mot de passe incorrect.</div>";
                return false; // Mauvais mot de passe
            }
        } else {
            echo "<div class='error-message'>Aucun utilisateur trouvé avec cet email.</div>";
            return false; // Utilisateur non trouvé
        }
    } catch (PDOException $e) {
        echo "Erreur lors de la connexion : " . $e->getMessage();
        return false;
    } finally {
        closeDB($pdo);
    }
}

// Nouvelle fonction pour vérifier si l'utilisateur est connecté
function isUserLoggedIn() {
    return isset($_SESSION['connectedUser']['id_client']);
}

?>