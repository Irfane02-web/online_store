 <?php
session_start();
require_once __DIR__ . "/includes/db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit("Identifiant du produit invalide.");
}

$sql = $connexion->prepare(
    "SELECT * FROM Produit WHERE id_produit = ?"
);
$sql->execute([$id]);
$produit = $sql->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    http_response_code(404);
    exit("Produit introuvable.");
}

$nom = $produit["nom_prouit"];
$image = basename($produit["image"] ?? "produit-defaut.jpg");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nom) ?> - Online Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">Online Store</a>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="panier.php">Mon panier</a>
        </nav>
    </header>

    <main class="container">
        <h1><?= htmlspecialchars($nom) ?></h1>

        <div class="detail-produit">
            <img
                src="images/produits/<?= htmlspecialchars($image) ?>"
                alt="<?= htmlspecialchars($nom) ?>"
                onerror="this.style.display='none'"
            >

            <div>
                <p class="prix">
                    <?= number_format((float)$produit["prix"], 2, ",", " ") ?> €
                </p>

                <p>
                    <?php
                    if (isset($produit["quantité"])) {
                        echo "Stock disponible : " .
                            (int)$produit["quantité"];
                    }
                    ?>
                </p>

                <form action="panier.php" method="POST">
                    <input type="hidden" name="id_produit"
                           value="<?= (int)$produit["id_produit"] ?>">

                    <label for="quantite">Quantité</label>
                    <input type="number" id="quantite" name="quantite"
                           value="1" min="1"
                           <?php if (isset($produit["quantité"])): ?>
                               max="<?= max(1, (int)$produit["quantité"]) ?>"
                           <?php endif; ?>
                           required>

                    <button type="submit" class="btn">
                        Ajouter au panier
                    </button>
                </form>
            </div>
        </div>

        <p><a href="index.php">← Retour à l'accueil</a></p>
    </main>
</body>
</html>