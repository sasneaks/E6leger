<?php
require_once './produits/listeProduits.php'; 
require_once './auth/fonctionlogin.php';
require_once './panier/ajouterPanier.php';
require_once './config/functions.php';




require_once './config/db_connect.php'; // Assure la connexion à la BDD


$cart_items = [];
$total_price=0;
if (isset($_SESSION['connectedUser'])) {
    $user_id = $_SESSION['connectedUser']['id_client'];
    $cart_items = getCartItems($user_id);
    foreach ($cart_items as $item) {
        $total_price += $item['prix'] * $item['quantite'];}
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site de sneakers</title>
    <link rel="stylesheet" href="css/style.css">
        <style>
        /* Styles pour l'affichage du prix et la sélection de quantité */
        .product-price {
            font-weight: bold;
            margin: 5px 0;
            font-size: 1.1em;
        }
        .quantity-selector {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px 0;
        }
        .quantity-selector select {
            padding: 5px;
            border-radius: 4px;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <div class="nav-container">
                <div class="logo img"> <img src="image/logosasneaks.png" alt="Sasneaks-logo"></div>
                <ul class="nav-links">
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#new-arrivals">Nouveautés</a></li>
                    <li><a href="#products">Produits</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="account-cart">

                    <!-- Panier à droite de la barre de navigation avec une icône et le nombre d'articles -->
                    <div class="cart">
                        <img src="image/cart-icon.png" alt="Panier" class="cart-icon">
                        <?php
                        if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id_client'])):
                            $user_id = $_SESSION['connectedUser']['id_client'];
                            $cart_count = getCartCount($user_id);
                        ?>
                        <span id="cart-count"><?php echo $cart_count ?></span> <!-- Indicateur du nombre d'articles dans le panier -->
                        <?php endif; ?>
                    </div>

                    <div class="account">
                        <img src="image/account-icon.webp" alt="compte" class="account-icon" id="account-icon">
                        <?php if (isUserLoggedIn()) { ?>
                            <span><?php echo htmlspecialchars($_SESSION['connectedUser']['identifiant']); ?></span>
                        <div class="account-dropdown" id="account-dropdown">
                            <ul>
                                <li><a href="profile/profile.php">Mon Profil</a></li>
                                <li><a href="config/logout.php">Se Déconnecter</a></li>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <a href="auth/login.php">Se connecter</a>
                    <?php } ?>
                    </div>

                    <!--Pop up du panier pour afficher les produits ajoutés-->
                   <!-- Pop up du panier -->
<div id="cart-modal" class="cart-popup">
    <div class="cart-popup-content">
        <span class="close-popup">&times;</span>
        <h3>Votre Panier</h3>
        <div id="cart-items">
            <?php if (!empty($cart_items)): ?>
                <ul>
                <?php foreach ($cart_items as $item): ?>
                    <li>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($item['nom']); ?>">
                        <div class="cart-item-details">
                            <span class="cart-item-name"><?php echo htmlspecialchars($item['nom']); ?></span>
                            <span class="cart-item-price"><?php echo number_format($item['prix'], 2, ',', ' '); ?> €</span>
                            <span>Quantité: <?php echo $item['quantite']; ?></span>
                        </div>
                        <form action="panier/supprimerPanier.php" method="POST">
                            <input type="hidden" name="produit_id" value="<?php echo $item['produit_id']; ?>">
                            <button type="submit" class="remove-btn">X</button>
                        </form>
                    </li>
                <?php endforeach; ?>
                </ul>
                <p class="total-quantite">Prix total du panier : <?php echo number_format($total_price, 2, ',', ' '); ?> €</p>
                
                <form action="commande/ajoutecommande.php" method="POST">
                    <button type="submit" id="checkout">Passer à la caisse</button>
                </form>
                

                <form action="panier/supprimerPanier.php" method="POST">
                    <button type="submit" name="emptycart" class="remove-all-btn">Vider le panier</button>
                </form>
            <?php else: ?>
                <p>Votre panier est vide.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


        </nav>
    </header>

    <section id="home">
    <div class="slider">
        <?php
        $sliderImages = getSliderImages();
        $active = 'active'; // Premier slide sera actif
        
        foreach ($sliderImages as $image): 
        ?>
            <div class="slide <?php echo $active; ?>">
                <img src="<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['nom']); ?>">
                <div class="caption"><?php echo htmlspecialchars($image['nom']); ?></div>
            </div>
        <?php 
            $active = ''; // Les slides suivants ne seront pas actifs
        endforeach; 
        ?>
        <button class="next"><span style='font-size:100px;'>❯</span></button>
        <button class="prev"><span style='font-size:100px;'>❮</span></button>
    </div>
</section>

    <section id="new-arrivals">
        <h2>Nouveautés</h2>
        <div class="product-grid">
            <?php

        $newproduits = getNouveauxProduits();

    // Vérification s'il y a des nouveaux produits
            // Si des nouveaux produits sont disponibles, on les affiche
            foreach ($newproduits as $produit): ?>
                <div class="product">
                    <!-- Ajout dynamique de l'image de survol via data-hover -->
                    <img src="<?php echo $produit['image_url']; ?>"
                        alt="<?php echo htmlspecialchars($produit['nom']); ?>" class="product-image" data-hover="images/product1-hover.jpg">
                    <p><?php echo htmlspecialchars($produit['nom']); ?></p>
                    <!-- Affichage du prix -->
                    <p class="product-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>

                    <form action="panier/controlAddPanier.php" method="POST">

                    <input type="hidden" id="id_product" name="id_product" value="<?php echo $produit['id_product'];?>">
                    <?php if (isUserLoggedIn()){ ?>
                        <input type="hidden" id="id_client" name="id_client" value="<?php echo htmlspecialchars($_SESSION['connectedUser']['id_client']);?>">
                    <?php
                    }
                    ?>
                    
                    <!-- Sélecteur de quantité -->
                    <div class="quantity-selector">
                        <label for="quantite-<?php echo $produit['id_product']; ?>">Quantité:</label>
                        <select id="quantite-<?php echo $produit['id_product']; ?>" name="quantite">
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" id="prix" name="prix" value="<?php echo $produit['prix'];?>">

                    <button type="submit" class="add-to-cart" >Ajouter au panier</button>

                </form>

                </div>
            <?php endforeach; ?>
         
        </div>
    </section>
    <section id="products">
        <h2>Tous les Produits</h2>
        <div class="product-grid">
            <?php
            $produits = getProduits();
            foreach($produits as $produit):?>
            <div class="product">
                    <!-- Ajout dynamique de l'image de survol via data-hover -->
                    <img src="<?php echo $produit['image_url']; ?>"
                        alt="<?php echo htmlspecialchars($produit['nom']); ?>" class="product-image" data-hover="images/product1-hover.jpg">
                    <p><?php echo htmlspecialchars($produit['nom']); ?></p>
                    <!-- Affichage du prix -->
                    <p class="product-price"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>

                    <form action="panier/controlAddPanier.php" method="POST">

                    <input type="hidden" id="id_product" name="id_product" value="<?php echo $produit['id_product'];?>">
                    <?php if (isUserLoggedIn()){ ?>
                        <input type="hidden" id="id_client" name="id_client" value="<?php echo htmlspecialchars($_SESSION['connectedUser']['id_client']);?>">
                    <?php
                    }
                    ?>
                    
                    <!-- Sélecteur de quantité -->
                    <div class="quantity-selector">
                        <label for="quantite-prod-<?php echo $produit['id_product']; ?>">Quantité:</label>
                        <select id="quantite-prod-<?php echo $produit['id_product']; ?>" name="quantite">
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" id="prix" name="prix" value="<?php echo $produit['prix'];?>">

                    <button type="submit" class="add-to-cart">Ajouter au panier</button>
                </form>

                </div>
            <?php endforeach; ?>
         
        </div>
 
    </section>
    <section id="contact">
        <h2>Contactez-nous</h2>
        <form id="contact-form">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="name" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
                <label for="e-mail">E-mail</label>
                <input type="text" id="e-mail" name="e-mail" placeholder="Votre E-mail" required>
            </div>
            <div class="form-group">
                <label for="nom">Message</label>
                <textarea name="message" id="msg" placeholder="Votre message" required></textarea>
            </div>
            <button type="submit">Envoyer</button>
        </form>
    </section>


    <footer>
        <div class="footer-container">
            <!-- Liens vers les différentes sections de la page -->
            <div class="footer-links">
                <a href="#home">Accueil</a>
                <a href="#new-arrivals">Nouveautés</a>
                <a href="#products">Produits</a>
                <a href="#contact">Contact</a>
            </div>
            <!-- Liens vers les réseaux sociaux -->
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank"><img src="image/facebook-icon.png" alt="Facebook"></a>
                <a href="https://twitter.com" target="_blank"><img src="image/x-icon.png" alt="x"></a>
            </div>
        </div>
        <p>&copy;2024 Sasneaks. Tous droits réservés.</p>
    </footer>
    <script src="js/script.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé');
    
    const accountIcon = document.getElementById('account-icon');
    const accountDropdown = document.getElementById('account-dropdown');
    
    console.log('Icon:', accountIcon);
    console.log('Dropdown:', accountDropdown);
    
    if (accountIcon && accountDropdown) {
        accountIcon.onclick = function() {
            console.log('Clic sur icône compte');
            if (accountDropdown.style.display === 'block') {
                accountDropdown.style.display = 'none';
            } else {
                accountDropdown.style.display = 'block';
            }
            return false;
        };
    }
});
</script>
<!-- Ajouter ceci juste avant la fermeture du body -->
<div id="product-modal" class="product-modal">
    <div class="product-modal-content">
        <span class="close-product-modal">&times;</span>
        <div class="product-modal-body">
            <div class="product-modal-image-container">
                <img id="modal-product-image" src="" alt="">
            </div>
            <div class="product-modal-details">
                <h2 id="modal-product-name"></h2>
                <p id="modal-product-price" class="modal-price"></p>
                <p id="modal-product-description"></p>
                <div class="modal-quantity-selector">
                    <label for="modal-quantite">Quantité:</label>
                    <select id="modal-quantite" name="quantite">
                        <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <form id="modal-add-to-cart-form" action="panier/controlAddPanier.php" method="POST">
                    <input type="hidden" id="modal-id-product" name="id_product" value="">
                    <?php if (isUserLoggedIn()){ ?>
                        <input type="hidden" id="modal-id-client" name="id_client" value="<?php echo htmlspecialchars($_SESSION['connectedUser']['id_client']);?>">
                    <?php } ?>
                    <input type="hidden" id="modal-prix" name="prix" value="">
                    <input type="hidden" id="modal-form-quantite" name="quantite" value="1">
                    <button type="submit" class="add-to-cart">Ajouter au panier</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>

</html>