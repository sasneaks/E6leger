<?php
session_start();
require_once("fonctionlogin.php");

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $pwd = trim($_POST['password']);

    if (!empty($email) && !empty($pwd)) {
        $data = ['email' => $email, 'password' => $pwd];

        if (login($data)) {
            $_SESSION['auth_email'] = $email; // Stocker temporairement l'email pour la 2FA
            header('Location: verify_2fa.php');
            exit;
        } else {
            $errorMessage = "Email ou mot de passe incorrect.";
        }
    } else {
        $errorMessage = "Tous les champs sont obligatoires.";
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Se connecter</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de Passe:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Connexion</button>
        </form>
        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
    </div>
</body>
</html>
