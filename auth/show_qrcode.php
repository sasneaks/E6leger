<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__.'/../vendor/autoload.php');

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Vérifier que temp_user existe
if (!isset($_SESSION['temp_user'])) {
    header('Location: register.php'); // Redirige si pas de session
    exit;
}

// Récupérer les informations
$email = $_SESSION['temp_user']['email'];
$secret = $_SESSION['temp_user']['secret'];

// Générer l'URL du QR Code
$qrCodeUrl = GoogleQrUrl::generate($email, $secret, 'Sasneaks'); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configurer Google Authenticator</title>
    <link rel="stylesheet" href="../css/show_qrcode.css">
</head>
<body>
    <div class="qrcode-container">
        <h2>Configurer votre double authentification</h2>

        <p>Scannez ce QR Code avec votre application Google Authenticator :</p>
        <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code 2FA">

        <p>Ou entrez ce code manuellement dans votre application :</p>
        <strong><?php echo htmlspecialchars($secret); ?></strong>

        <form action="verify_2fa.php" method="POST">
            <label for="code">Entrez le code généré :</label>
            <input type="text" name="code" id="code" required>
            <button type="submit">Valider le code</button>
        </form>
    </div>
</body>
</html>
