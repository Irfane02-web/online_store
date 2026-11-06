<?php
session_start();

require_once __DIR__ . "/includes/db.php";

// Récupérer les produits enregistrés dans la base de données
$sql = "SELECT * FROM Produit LIMIT 8";
$resultat = $connexion->query($sql);
$produits = $resultat->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Store | Votre boutique en ligne</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- En-tête -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                Online<span>Store</span>
            </a>

            <nav class="navigation">
                <a href="index.php" class="active">Accueil</a>
                <a href="#produits">Nos produits</a>
                <a href="#categories">Catégories</a>
            </nav>

            <div class="header-actions">
                <a href="connexion.php" class="btn-connexion">
                    Connexion
                </a>

                <a href="inscription.php" class="btn-inscription">
                    Créer un compte
                </a>

                <a href="panier.php" class="btn-panier">
                    <span aria-hidden="true">🛒</span> Panier
                </a>
            </div>
        </div>
    </header>

    <main>

        <!-- Bannière principale -->
        <section class="hero">
            <div class="hero-content">
                <span class="hero-tag">BIENVENUE CHEZ ONLINE STORE</span>

                <h1>Tout ce que vous aimez,<br>
                    <span>au même endroit.</span>
                </h1>

                <p>
                    Découvrez notre sélection de produits et trouvez
                    facilement ce qu'il vous faut.
                </p>

                <a href="#produits" class="btn-principal">
                    Découvrir les produits <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="hero-decoration" aria-hidden="true">
                <div class="hero-circle">OS</div>
            </div>
        </section>

        <!-- Catégories -->
         <section id="categories">

            <h2>Nos catégories</h2>

            <p>Choisis le type de matériel qui t'intéresse.</p>

            <div class="categories">

                <!-- Première catégorie -->
                <article class="categorie">
                    <h3>Écrans</h3>
                    <p>Découvre nos écrans informatiques.</p>
                    <a href="#produits">Voir les produits</a>
                </article>

                <!-- Deuxième catégorie -->
                <article class="categorie">
                    <h3>Claviers</h3>
                    <p>Des claviers pour travailler ou jouer.</p>
                    <a href="#produits">Voir les produits</a>
                </article>

                <!-- Troisième catégorie -->
                <article class="categorie">
                    <h3>Souris</h3>
                    <p>Choisis la souris adaptée à tes besoins.</p>
                    <a href="C:\MAMP\htdocs\Online_Store\Images\Produits\clavier.jpg">Voir les produits</a>
                </article>

                <!-- Quatrième catégorie -->
                <article class="categorie">
                    <h3>Unités centrales</h3>
                    <p>Découvre nos ordinateurs de bureau.</p>
                    <a href="#produits">Voir les produits</a>
                </article>

            </div>

        </section>

        <!-- Produits -->
<section class="products-section section-container" id="produits">

    <div class="section-heading">
        <div>
            <span class="section-tag">NOTRE SÉLECTION</span>
            <h2>Nos produits</h2>
        </div>

        <p>Découvrez notre sélection de matériel informatique.</p>
    </div>

    <div class="products-grid">

        <!-- Clavier -->
        <article class="product-card">
            <a href="produit.php" class="product-image">
                <img
                    src="Images/Produits/clavier.jpg"
                    alt="Clavier informatique"
                    loading="lazy"
                >
            </a>

            <div class="product-info">
                <h3>Claviers</h3>
                <p class="product-description">
                    Des claviers pour travailler ou jouer.
                </p>

                <div class="product-bottom">
                    <span class="product-category">Informatique</span>
                    <a href="produit.php" class="btn-voir" aria-label="Découvrir les claviers">→</a>
                </div>
            </div>
        </article>

        <!-- Souris -->
        <article class="product-card">
            <a href="produit.php" class="product-image">
                <img
                    src="Images/Produits/souris.jpg"
                    alt="Souris informatique"
                    loading="lazy"
                >
            </a>

            <div class="product-info">
                <h3>Souris</h3>
                <p class="product-description">
                    Choisissez une souris adaptée à vos besoins.
                </p>

                <div class="product-bottom">
                    <span class="product-category">Informatique</span>
                    <a href="produit.php" class="btn-voir" aria-label="Découvrir les souris">→</a>
                </div>
            </div>
        </article>

        <!-- Écran -->
        <article class="product-card">
            <a href="produit.php" class="product-image">
                <img
                    src="Images/Produits/ecran.jpg"
                    alt="Écran informatique"
                    loading="lazy"
                >
            </a>

            <div class="product-info">
                <h3>Écrans</h3>
                <p class="product-description">
                    Profitez d'un affichage confortable et précis.
                </p>

                <div class="product-bottom">
                    <span class="product-category">Informatique</span>
                    <a href="produit.php" class="btn-voir" aria-label="Découvrir les écrans">→</a>
                </div>
            </div>
        </article>

        <!-- Unité centrale -->
        <article class="product-card">
            <a href="produit.php" class="product-image">
                <img
                    src="Images/Produits/ordinateur.jpg"
                    alt="Ordinateur de bureau et unité centrale"
                    loading="lazy"
                >
            </a>

            <div class="product-info">
                <h3>Unités centrales</h3>
                <p class="product-description">
                    Découvrez nos ordinateurs de bureau.
                </p>

                <div class="product-bottom">
                    <span class="product-category">Informatique</span>
                    <a href="produit.php" class="btn-voir" aria-label="Découvrir les unités centrales">→</a>
                </div>
            </div>
        </article>

    </div>
</section>

        <!-- Bandeau de confiance -->
        <section class="benefits">
            <div class="benefit">
                <span aria-hidden="true">🚚</span>
                <div>
                    <h3>Livraison</h3>
                    <p>Informations sur la livraison à venir</p>
                </div>
            </div>

            <div class="benefit">
                <span aria-hidden="true">🔒</span>
                <div>
                    <h3>Achat sécurisé</h3>
                    <p>Vos achats en toute tranquillité</p>
                </div>
            </div>

            <div class="benefit">
                <span aria-hidden="true">💬</span>
                <div>
                    <h3>À votre écoute</h3>
                    <p>Une boutique proche de ses clients</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Pied de page -->
    <footer class="footer">
        <div class="footer-container">
            <a href="index.php" class="logo footer-logo">
                Online<span>Store</span>
            </a>

            <p>Votre boutique en ligne, simplement.</p>

            <div class="footer-links">
                <a href="index.php">Accueil</a>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>
                <a href="panier.php">Panier</a>
            </div>
        </div>

        <div class="footer-bottom">
            © <?= date("Y") ?> Online Store — Tous droits réservés.
        </div>
    </footer>

</body>
</html>