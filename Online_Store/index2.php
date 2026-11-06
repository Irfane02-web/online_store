
<?php
session_start();

// Vérifier que le client est connecté
if (!isset($_SESSION["client"])) {
    header("Location: connexion.php");
    exit;
}

// Informations du client connecté
$client = $_SESSION["client"];

// Connexion à la base de données
require_once "includes/db.php";

// Récupérer les produits depuis la base de données
$sql = "
    SELECT id_produit, nom_produit, prix, quantite
    FROM produit
    ORDER BY id_produit DESC
    LIMIT 8
";

$resultat = $connexion->query($sql);
$produits = $resultat->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour choisir une image selon le nom du produit
function choisirImage($nom)
{
    $nomRecherche = mb_strtolower($nom, "UTF-8");

    if (mb_strpos($nomRecherche, "clavier") !== false) {
        return "Images/Produits/clavier.jpg";
    }

    if (mb_strpos($nomRecherche, "souris") !== false) {
        return "Images/Produits/souris.jpg";
    }

    if (
        mb_strpos($nomRecherche, "écran") !== false ||
        mb_strpos($nomRecherche, "ecran") !== false
    ) {
        return "Images/Produits/ecran.jpg";
    }

    return "Images/Produits/ordinateur.jpg";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>OnlineStore - Accueil</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- En-tête du site -->
    <header class="header">
        <div class="container header-container">

            <a href="index2.php" class="logo">
                OnlineStore
            </a>

            <nav class="navbar">
                <a href="index2.php">Accueil</a>
                <a href="#produits">Nos produits</a>
                <a href="#categories">Catégories</a>
            </nav>

            <div class="header-actions">
                <span class="welcome-user">
                    Bonjour,
                    <?= htmlspecialchars(
                        $client["prenom"] ?? $client["nom"] ?? "Client",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </span>

                <a href="panier.php" class="btn-panier">
                    Mon panier
                </a>

                <a href="deconnexion.php" class="btn-deconnexion">
                    Déconnexion
                </a>
            </div>

        </div>
    </header>


    <!-- Section d'accueil -->
    <main>

        <section class="hero">
            <div class="container hero-content">

                <div class="hero-text">
                    <p class="hero-subtitle">
                        Bienvenue chez OnlineStore
                    </p>

                    <h1>
                        Tout votre matériel informatique au même endroit
                    </h1>

                    <p>
                        Découvrez notre sélection de matériel informatique
                        et trouvez les équipements adaptés à vos besoins.
                    </p>

                    <a href="#produits" class="btn-principal">
                        Découvrir nos produits
                    </a>
                </div>

            </div>
        </section>


        <!-- Section catégories -->
        <section
            class="categories-section section-container"
            id="categories"
        >
            <div class="section-heading">
                <p class="section-subtitle">Explorez notre boutique</p>
                <h2>Nos catégories</h2>
                <p>
                    Retrouvez les équipements informatiques dont vous avez besoin.
                </p>
            </div>

            <div class="categories-grid">

                <article class="category-card">
                    <div class="category-icon">⌨️</div>
                    <h3>Claviers</h3>
                    <p>Pour travailler, étudier ou jouer.</p>
                    <a href="#produits" class="btn-voir">
                        Voir les produits
                    </a>
                </article>

                <article class="category-card">
                    <div class="category-icon">🖱️</div>
                    <h3>Souris</h3>
                    <p>Des souris pour tous vos usages.</p>
                    <a href="#produits" class="btn-voir">
                        Voir les produits
                    </a>
                </article>

                <article class="category-card">
                    <div class="category-icon">🖥️</div>
                    <h3>Écrans</h3>
                    <p>Améliorez votre confort visuel.</p>
                    <a href="#produits" class="btn-voir">
                        Voir les produits
                    </a>
                </article>

                <article class="category-card">
                    <div class="category-icon">💻</div>
                    <h3>Ordinateurs</h3>
                    <p>Le matériel adapté à vos projets.</p>
                    <a href="#produits" class="btn-voir">
                        Voir les produits
                    </a>
                </article>

            </div>
        </section>


        <!-- Section produits -->
        <section
            class="products-section section-container"
            id="produits"
        >
            <div class="section-heading">
                <p class="section-subtitle">Notre sélection</p>
                <h2>Nos produits</h2>
                <p>
                    Consultez les prix et les disponibilités de nos produits.
                </p>
            </div>

            <div class="products-grid">

                <?php if (empty($produits)): ?>

                    <p>
                        Aucun produit n'est disponible pour le moment.
                    </p>

                <?php else: ?>

                    <?php foreach ($produits as $produit): ?>

                        <?php
                        $id = (int) $produit["id_produit"];

                        $nom = $produit["nom_produit"] ?? "Produit";

                        $prix = (float) ($produit["prix"] ?? 0);

                        $stock = max(
                            0,
                            (int) ($produit["quantite"] ?? 0)
                        );

                        $image = choisirImage($nom);
                        ?>

                        <article class="product-card">

                            <div class="product-image">
                                <img
                                    src="<?= htmlspecialchars(
                                        $image,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                        $nom,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>"
                                    loading="lazy"
                                >
                            </div>

                            <div class="product-info">

                                <h3>
                                    <?= htmlspecialchars(
                                        $nom,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </h3>

                                <p class="product-description">
                                    Matériel informatique disponible
                                    chez OnlineStore.
                                </p>

                                <p class="product-price">
                                    <strong>
                                        <?= number_format(
                                            $prix,
                                            2,
                                            ",",
                                            " "
                                        ) ?> €
                                    </strong>
                                </p>

                                <p class="product-stock">
                                    <?php if ($stock > 0): ?>
                                        <span>
                                            <?= $stock ?> en stock
                                        </span>
                                    <?php else: ?>
                                        <span>
                                            Indisponible
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <?php if ($stock > 0): ?>

                                    <form
                                        action="panier.php"
                                        method="post"
                                        class="product-cart-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="ajouter"
                                        >

                                        <input
                                            type="hidden"
                                            name="id_produit"
                                            value="<?= $id ?>"
                                        >

                                        <label for="quantite-<?= $id ?>">
                                            Quantité
                                        </label>

                                        <input
                                            type="number"
                                            id="quantite-<?= $id ?>"
                                            name="quantite"
                                            value="1"
                                            min="1"
                                            max="<?= $stock ?>"
                                            required
                                        >

                                        <button
                                            type="submit"
                                            class="btn-principal"
                                        >
                                            Ajouter au panier
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <button type="button" disabled>
                                        Rupture de stock
                                    </button>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>

    </main>


    <!-- Pied de page -->
    
<footer class="footer">
    <div class="footer-main">

        <!-- Présentation de la boutique -->
        <div class="footer-brand">
            <a href="index2.php" class="footer-logo">
                Online<span>Store</span>
            </a>

            <p>
                Votre boutique de matériel informatique.
                Découvrez nos équipements pour étudier,
                travailler et vous divertir.
            </p>

            <div class="footer-social">
                <a href="mailto:contact@onlinestore.fr"
                   aria-label="Nous contacter par email">
                    ✉
                </a>
                <a href="#produits" aria-label="Découvrir nos produits">
                    🛒
                </a>
                <a href="#categories" aria-label="Voir nos catégories">
                    💻
                </a>
            </div>
        </div>

        <!-- Navigation -->
        <div class="footer-column">
            <h3>Navigation</h3>
            <ul>
                <li><a href="index2.php">Accueil</a></li>
                <li><a href="#produits">Nos produits</a></li>
                <li><a href="#categories">Catégories</a></li>
                <li><a href="panier.php">Mon panier</a></li>
            </ul>
        </div>

        <!-- Assistance -->
        <div class="footer-column">
            <h3>Votre compte</h3>
            <ul>
                <li>
                    <a href="index2.php">Mon espace client</a>
                </li>
                <li>
                    <a href="panier.php">Mes achats</a>
                </li>
                <li>
                    <a href="deconnexion.php">Déconnexion</a>
                </li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="footer-column footer-contact">
            <h3>Besoin d'aide ?</h3>

            <p>
                Une question sur notre boutique ?
                N'hésitez pas à nous contacter.
            </p>

            <a href="mailto:contact@onlinestore.fr"
               class="footer-contact-button">
                Nous contacter
            </a>
        </div>

    </div>

    <!-- Barre inférieure -->
    <div class="footer-bottom">
        <p>
            &copy; <?= date("Y") ?> OnlineStore.
            Tous droits réservés.
        </p>

        <p class="footer-made">
            Conçu avec soin pour vos besoins informatiques.
        </p>
    </div>
</footer>


</body>
</html>
