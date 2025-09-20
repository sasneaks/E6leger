<?php
require_once("../auth/fonctionLogin.php");
require_once('function_dashboard.php');
require_once("../config/db_connect.php");
require_once('gestion_products.php'); 




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id_client = $_POST['id_client'];
    $identifiant = $_POST['identifiant'];
    $email = $_POST['email'];


    // Mise à jour des informations de l'utilisateur dans la base de données
    $stmt = $pdo->prepare('UPDATE users SET identifiant = ?, email = ? WHERE id_client = ?');
    $stmt->execute([$identifiant, $email, $id_client]);

    // Redirection après la mise à jour
    header("Location: dashboard.php");

    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id_produit = $_POST['id_product'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];


    // Mise à jour des informations de l'utilisateur dans la base de données
    $stmt = $pdo->prepare('UPDATE products SET nom = ?, description = ? WHERE id_product = ?');
    $stmt->execute([$nom, $description, $id_produit]);

    // Redirection après la mise à jour
    header("Location: dashboard.php");

    exit;
}







?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="../css/profileadm.css">
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2> Mon Profil</h2>
            <ul>
                <li><a href="dashboard.php">THE ADMIN DASHBOARD</a></li>
                <li><a href="#gproducts">Gestion des Produits</a></li>
                <li><a href="#gclients">Gestion des Clients</a></li>
                <li><a href="#Gemployes">Gestion des Employés</a></li>
                <li><a href="#Gorders">Gestion des Commandes</a></li>
                <li><a href="../config/logout.php" class="logout-btn">Se Déconnecter</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>THE ADMIN DASHBOARD</h1>

            <div id="gproducts" class="gproducts">
                <h2>Gestion des Produits</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID Produit</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Image URL</th>
                            <th>Date de Sortie</th>
                            <th>image_hover_url</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <form method="POST" action="dashboard.php">
                                        <td><?php echo htmlspecialchars($product['id_product']); ?></td>
                                        <td>
                                            <input type="text" name="nom"
                                                value="<?php echo htmlspecialchars($product['nom']); ?>">
                                        </td>
                                        <td>
                                            <textarea
                                                name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                        </td>
                                        <td>
                                            <input type="text" name="prix"
                                                value="<?php echo htmlspecialchars($product['prix']); ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="image_url"
                                                value="<?php echo htmlspecialchars($product['image_url']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['date_sortie']); ?></td>
                                        <td>
                                            <input type="text" name="image_hover_url"
                                                value="<?php echo htmlspecialchars($product['image_hover_url']); ?>">
                                        </td>
                                        <td>
                                            <!-- Champ caché pour l'ID produit -->
                                            <input type="hidden" name="id_product"
                                                value="<?php echo htmlspecialchars($product['id_product']); ?>">
                                            <button type="submit" name="modifier">Modifier</button>
                                        </td>
                                    </form>

                                    <!-- Bouton Supprimer -->
                                    <td>
                                        <a href="deleteProduct.php?delete=<?= $product['id_product'] ?>"
                                            onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');"
                                            id="deletebtn">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">Aucun produit trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>


            </div>
            <div id="gclients" class="gclients">
                <h2>Gestion des Utilisateurs</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Date de création</th>
                            <th>Role</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <form method="post" action="roleedit.php">
                                        <td><?php echo htmlspecialchars($user['id_client']); ?></td>
                                        <td>
                                            <input type="text" name="identifiant"
                                                value="<?php echo htmlspecialchars($user['identifiant']); ?>">
                                        </td>
                                        <td>
                                            <input type="email" name="email"
                                                value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        <td>
                                            <select name="role" class="modern-select">
                                                <option value="employee" <?php echo ($user['role'] === 'employee') ? 'selected' : ''; ?>>Employé</option>
                                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="client" <?php echo ($user['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                                            </select>
                                        </td>
                                        <td>
                                            <!-- Champ caché pour l'ID client -->
                                            <input type="hidden" name="id_client"
                                                value="<?php echo htmlspecialchars($user['id_client']); ?>">
                                            <button type="submit">Modifier</button>
                                        </td>
                                    </form>

                                    <td>
                                        <a href="deleteUser.php?delete=<?= $user['id_client'] ?>"
                                            onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');"
                                            id='deletbtn'> Supprimer</a>
                                    </td>
                                </tr>
                                </form>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7s">Aucun utilisateur trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
            <!-- Section Informations personnelle -->
            <div id="Gemployes" class="Gemployes">
                <h2>Gestion des Employés</h2>
                <p>Nom: <?php echo htmlspecialchars($_SESSION['connectedUser']['identifiant']); ?></p>
                <p>Email: <?php echo htmlspecialchars($_SESSION['connectedUser']['email']); ?></p>
                <button class="edit-btn">Modifier Profil</button>
            </div>
            <!-- Section Méthodes de paiement -->
            <div id="Gorders" class="Gorders">
                <h2>Gestion des Commandes</h2>
                <?php
                if (!empty($commandes)) {
                    foreach ($commandes as $res) { ?>
                        <div class="order-item">
                            <h3>Commande #<?php echo htmlspecialchars($res['id']); ?></h3>
                            <p>Statut: <?php echo htmlspecialchars($res['statut']); ?></p>
                            <p>Total : <?php echo htmlspecialchars($res['montant_total']); ?> €</p>
                        </div>
                    <?php }
                } else { ?>
                    <p></p><?php } ?>
                <!-- Ajouter les méthodes de paiement ici -->

            </div>

        </div>
    </div>
</body>