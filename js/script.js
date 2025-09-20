document.addEventListener('DOMContentLoaded', function() {
    // Variables du slider
    let slideIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    let slideInterval;

    // Vérifier si les éléments existent
    if (!slides.length) {
        console.error('Aucun slide trouvé');
        return;
    }

    // Fonction d'initialisation - définir le premier slide comme actif
    function initSlider() {
        if (slides.length > 0) {
            slides[0].classList.add('active');
        }
    }

    // Fonction pour afficher un slide spécifique
    function showSlide(index) {
        // Cacher tous les slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        
        // Afficher le slide demandé
        slides[index].classList.add('active');
        slideIndex = index;
    }

    // Fonction pour passer au slide suivant
    function nextSlide() {
        let nextIndex = (slideIndex + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Fonction pour revenir au slide précédent
    function prevSlide() {
        let prevIndex = (slideIndex - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    // Événements pour les boutons précédent/suivant
    if (prevButton) {
        prevButton.addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(slideInterval);
            prevSlide();
            startAutoSlide();
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(slideInterval);
            nextSlide();
            startAutoSlide();
        });
    }

    // Démarrer le défilement automatique
    function startAutoSlide() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    // Initialiser le slider
    initSlider();
    startAutoSlide();

    // Arrêter le défilement automatique au survol
    const sliderContainer = document.querySelector('.slider');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', function() {
            clearInterval(slideInterval);
        });
        
        sliderContainer.addEventListener('mouseleave', function() {
            startAutoSlide();
        });
    }
});



// Cela nous permet de manipuler ces images lors d'événements comme le survol de la souris.
const productImages = document.querySelectorAll('.product-image');

// Utilisation de forEach pour parcourir toutes les images des produits.
productImages.forEach((image) => {
    // Stocke l'URL de l'image actuelle (celle par défaut) dans une variable 'originalSrc'.
    const originalSrc = image.src;

    // Récupère l'attribut 'data-hover' qui contient l'URL de l'image à afficher lorsque la souris passe dessus.
    const hoverSrc = image.getAttribute('data-hover');

    // Ajout d'un événement 'mouseover' (quand la souris passe au-dessus de l'image).
    // Lorsqu'on survole l'image, elle change pour l'image définie dans 'hoverSrc'.
    image.addEventListener('mouseover', () => {
        image.src = hoverSrc;
    });

    // Ajout d'un événement 'mouseout' (quand la souris quitte l'image).
    // Lorsque la souris quitte l'image, elle revient à l'image d'origine stockée dans 'originalSrc'.
    image.addEventListener('mouseout', () => {
        image.src = originalSrc;
    });
});


// Script pour basculer l'affichage du sous-menu du compte
document.addEventListener('DOMContentLoaded', function() {
    const accountIcon = document.getElementById('account-icon');
    const accountDropdown = document.getElementById('account-dropdown');
    
    if (accountIcon && accountDropdown) {
        accountIcon.addEventListener('click', function(event) {
            accountDropdown.classList.toggle('active'); // Utiliser 'active' au lieu de 'show'
            event.stopPropagation(); // Empêcher la propagation du clic
        });
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!accountDropdown.contains(event.target) && !accountIcon.contains(event.target)) {
                accountDropdown.classList.remove('active');
            }
        });
    }
});


const cartModal = document.getElementById('cart-modal');
const addToCartButtons = document.querySelectorAll('.add-to-cart');
const closeCartModalButton = document.querySelector('#cart-modal .close-popup');
const cartIcon = document.querySelector('.cart');


    function showCartModal() {
        cartModal.style.display = 'block';
    }

    // Fermer la popup du panier lorsqu'on clique sur "X"
    closeCartModalButton.addEventListener('click', () => {
        cartModal.style.display = 'none';
    });

    // Fermer la popup en cliquant en dehors de celle-ci
    window.addEventListener('click', (event) => {
        if (event.target === cartModal) {
            cartModal.style.display = 'none';
        }
    });

    // Afficher le panier lorsqu'on clique sur l'icône du panier
    cartIcon.addEventListener('click', showCartModal);

 





// Modal pour détail produit
document.addEventListener('DOMContentLoaded', function() {
    const productItems = document.querySelectorAll('.product');
    const productModal = document.getElementById('product-modal');
    const closeModal = document.querySelector('.close-product-modal');
    const modalImage = document.getElementById('modal-product-image');
    const modalName = document.getElementById('modal-product-name');
    const modalPrice = document.getElementById('modal-product-price');
    const modalDescription = document.getElementById('modal-product-description');
    const modalIdProduct = document.getElementById('modal-id-product');
    const modalPrix = document.getElementById('modal-prix');
    const modalQuantiteSelect = document.getElementById('modal-quantite');
    const modalFormQuantite = document.getElementById('modal-form-quantite');
    
    // Fonction pour ouvrir la modal avec les détails du produit
    function openProductModal(product) {
        // Récupérer les informations du produit
        const productImage = product.querySelector('.product-image').src;
        const productName = product.querySelector('p:first-of-type').textContent;
        const productPrice = product.querySelector('.product-price').textContent;
        const productId = product.querySelector('input[name="id_product"]').value;
        const productPrixValue = product.querySelector('input[name="prix"]').value;
        
        // Essayer de récupérer la description (si disponible via un attribut data)
        const productDescription = product.getAttribute('data-description') || 
                                   "Détails du produit non disponibles pour le moment.";
        
        // Mettre à jour la modal avec ces informations
        modalImage.src = productImage;
        modalImage.alt = productName;
        modalName.textContent = productName;
        modalPrice.textContent = productPrice;
        modalDescription.textContent = productDescription;
        modalIdProduct.value = productId;
        modalPrix.value = productPrixValue;
        
        // Afficher la modal
        productModal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Empêcher le scroll de la page
    }
    
    // Ajouter l'événement de clic à chaque produit
    productItems.forEach(product => {
        product.addEventListener('click', function(e) {
            // Ne pas déclencher si on clique sur le bouton d'ajout au panier
            if (!e.target.closest('.add-to-cart') && !e.target.closest('form')) {
                e.preventDefault();
                openProductModal(this);
            }
        });
    });
    
    // Mettre à jour la quantité du formulaire lorsqu'elle change
    if (modalQuantiteSelect) {
        modalQuantiteSelect.addEventListener('change', function() {
            modalFormQuantite.value = this.value;
        });
    }
    
    // Fermer la modal
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            productModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Réactiver le scroll
        });
    }
    
    // Fermer la modal en cliquant en dehors
    window.addEventListener('click', function(e) {
        if (e.target === productModal) {
            productModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});



