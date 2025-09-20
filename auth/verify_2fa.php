<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__.'/../vendor/autoload.php');
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    if (!isset($_SESSION['temp_user']['secret'])) {
        die("Erreur : Vous devez être connecté pour vérifier le code 2FA.");
    }
    $secret = $_SESSION['temp_user']['secret'];
    
    $gAuth = new GoogleAuthenticator();
    
    if ($gAuth->checkCode($secret, $code)) {
        // Authentification réussie
        $_SESSION['connectedUser'] = [
            'id_client' => $_SESSION['temp_user']['id_client'],
            'identifiant' => $_SESSION['temp_user']['identifiant'],
            'email' => $_SESSION['temp_user']['email'],
            'role' => $_SESSION['temp_user']['role']
        ];

        $_SESSION['2fa_verified'] = true;

        // Nettoyage
        unset($_SESSION['temp_user']);
        
      // Marquer l'authentification 2FA comme vérifiée
        header('Location: ../index.php');
        exit;
    } else {
        $error = "Code 2FA invalide.";
    }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification 2FA</title>
    <link rel="stylesheet" href="../css/verify_2fa.css">
</head>
<body>
    <div class="verify-2fa-container">
        <h2>Vérification Google Authenticator</h2>
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="verify_2fa.php">
            <label for="code">Code 2FA :</label>
            <input type="text" id="code" name="code" required>
            <button type="submit">Vérifier</button>
        </form>
    </div>
</body>
</html>
