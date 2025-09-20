<?php
session_start();
require_once "fonctionInscription.php";

// Rediriger l'utilisateur s'il est déjà connecté
if (isset($_SESSION['connectedUser'])) {
    header("Location: ../index.php");
    exit;
}

$errorMessage = "";
$successMessage = "";

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $pwd = trim($_POST['pwd']);

    if (!empty($nom) && !empty($email) && !empty($pwd)) {
        // Vérification si l'utilisateur existe déjà
        if (checkExistUser($email)) {
            $errorMessage = "Un compte avec cet email existe déjà.";
        } else {
            // Données à insérer
            $data = [
                'nom' => $nom,
                'email' => $email,
                'password' => $pwd
            ];

            if (insertUser($data)) {
                $_SESSION['qrcode_email'] = $email;
                header('Location: show_qrcode.php');
                exit;
            
            } else {
                $errorMessage = "Un problème est survenu. Veuillez réessayer plus tard.";
            }
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
    <title>Inscription</title>
    <link rel="stylesheet" href="../css/register.css">
    <script>
        function validatePassword() {
            const password = document.getElementById("pwd").value;
            const errorMessage = document.getElementById("password-error");
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/;

            if (!passwordPattern.test(password)) {
                errorMessage.textContent = "Le mot de passe doit contenir au moins 12 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.";
                return false;
            }
            errorMessage.textContent = "";
            return true;
        }

        function validateEmail() {
            const email = document.getElementById("email").value;
            const errorMessage = document.getElementById("email-error");
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailPattern.test(email)) {
                errorMessage.textContent = "Veuillez saisir une adresse email valide.";
                return false;
            }
            errorMessage.textContent = "";
            return true;
        }

        function handleSubmit(event) {
            if (!validatePassword() || !validateEmail()) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <div class="signup-container">
        <h2>Inscription</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form action="#" method="post" onsubmit="handleSubmit(event)">
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required oninput="validateEmail()">
                <div id="email-error" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="pwd">Mot de Passe:</label>
                <input type="password" id="pwd" name="pwd" required oninput="validatePassword()">
                <div id="password-error" class="error-message"></div>
            </div>
            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>
