
<?php
session_start();

if (!isset($_SESSION["client"])) {
    header("Location: connexion.php");
    exit;
}

require_once "includes/db.php";

$client = $_SESSION["client"];
$idClient = (int)$client["id"];

$message = "";
$typeMessage = "";

function obtenirProduit(PDO $connexion, int $idProduit): ?array
{
    $requete = $connexion->prepare(
        "SELECT id_produit, nom_produit, prix, quantite
         FROM produit
         WHERE id_produit = ?"
    );
    $requete->execute([$idProduit]);

    $produit = $requete->fetch(PDO::FETCH_ASSOC);

    return $produit ?: null;
}

function obtenirQuantitePanier(
    PDO $connexion,
    int $idClient,
    int $idProduit
): int {
    $requete = $connexion->prepare(
        "SELECT quantite FROM panier
         WHERE id_client = ? AND id_produit = ?"
    );
    $requete->execute([$idClient, $idProduit]);

    return (int)$requete->fetchColumn();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // Ajouter un produit au panier
    if ($action === "ajouter") {
        $idProduit = filter_input(INPUT_POST, "id_produit", FILTER_VALIDATE_INT);
        $quantite = filter_input(INPUT_POST, "quantite", FILTER_VALIDATE_INT);

        if (!$idProduit || !$quantite || $quantite < 1) {
            $message = "Produit ou quantité invalide.";
            $typeMessage = "erreur";
        } else {
            $produit = obtenirProduit($connexion, $idProduit);

            if (!$produit || (int)$produit["quantite"] < 1) {
                $message = "Ce produit est indisponible.";
                $typeMessage = "erreur";
            } else {
                $quantiteActuelle = obtenirQuantitePanier(
                    $connexion,
                    $idClient,
                    $idProduit
                );

                $nouvelleQuantite = $quantiteActuelle + $quantite;

                if ($nouvelleQuantite > (int)$produit["quantite"]) {
                    $message = "Stock insuffisant. Stock disponible : "
                             . (int)$produit["quantite"] . ".";
                    $typeMessage = "erreur";
                } else {
                    $requete = $connexion->prepare(
                        "INSERT INTO panier (id_client, id_produit, quantite)
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)"
                    );
                    $requete->execute([
                        $idClient,
                        $idProduit,
                        $nouvelleQuantite
                    ]);

                    $message = "Produit ajouté au panier !";
                    $typeMessage = "succes";
                }
            }
        }
    }

    // Modifier la quantité
    elseif ($action === "modifier") {
        $idProduit = filter_input(INPUT_POST, "id_produit", FILTER_VALIDATE_INT);
        $quantite = filter_input(INPUT_POST, "quantite", FILTER_VALIDATE_INT);

        if (
            !$idProduit ||
            obtenirQuantitePanier($connexion, $idClient, $idProduit) < 1
        ) {
            $message = "Produit introuvable dans ton panier.";
            $typeMessage = "erreur";
        } elseif (!$quantite || $quantite < 1) {
            $message = "La quantité doit être supérieure à zéro.";
            $typeMessage = "erreur";
        } else {
            $produit = obtenirProduit($connexion, $idProduit);

            if (!$produit) {
                $message = "Ce produit n'existe plus.";
                $typeMessage = "erreur";
            } elseif ($quantite > (int)$produit["quantite"]) {
                $message = "Stock insuffisant. Stock disponible : "
                         . (int)$produit["quantite"] . ".";
                $typeMessage = "erreur";
            } else {
                $requete = $connexion->prepare(
                    "UPDATE panier
                     SET quantite = ?
                     WHERE id_client = ? AND id_produit = ?"
                );
                $requete->execute([$quantite, $idClient, $idProduit]);

                $message = "Quantité mise à jour.";
                $typeMessage = "succes";
            }
        }
    }

    // Supprimer un produit
    elseif ($action === "supprimer") {
        $idProduit = filter_input(INPUT_POST, "id_produit", FILTER_VALIDATE_INT);

        if ($idProduit) {
            $requete = $connexion->prepare(
                "DELETE FROM panier
                 WHERE id_client = ? AND id_produit = ?"
            );
            $requete->execute([$idClient, $idProduit]);

            $message = "Produit supprimé du panier.";
            $typeMessage = "succes";
        }
    }

    // Vider uniquement le panier du client connecté
    elseif ($action === "vider") {
        $requete = $connexion->prepare(
            "DELETE FROM panier WHERE id_client = ?"
        );
        $requete->execute([$idClient]);

        $message = "Ton panier a été vidé.";
        $typeMessage = "succes";
    }

    // Valider la commande
    elseif ($action === "commander") {
        try {
            $connexion->beginTransaction();

            // Charger et verrouiller les articles du panier
            $requete = $connexion->prepare(
                "SELECT p.id_produit, p.nom_produit, p.prix,
                        p.quantite AS stock, pa.quantite AS quantite_panier
                 FROM panier pa
                 INNER JOIN produit p ON p.id_produit = pa.id_produit
                 WHERE pa.id_client = ?
                 FOR UPDATE"
            );
            $requete->execute([$idClient]);
            $articlesCommande = $requete->fetchAll(PDO::FETCH_ASSOC);

            if (empty($articlesCommande)) {
                throw new RuntimeException("Ton panier est vide.");
            }

            $total = 0;

            foreach ($articlesCommande as $article) {
                if (
                    (int)$article["quantite_panier"] < 1 ||
                    (int)$article["quantite_panier"] > (int)$article["stock"]
                ) {
                    throw new RuntimeException(
                        "Stock insuffisant pour : " . $article["nom_produit"]
                    );
                }

                $total += (float)$article["prix"]
                        * (int)$article["quantite_panier"];
            }

            // Les identifiants actuels ne sont pas AUTO_INCREMENT.
            // Cette méthode est à remplacer idéalement par AUTO_INCREMENT.
            $prochainId = (int)$connexion->query(
                "SELECT COALESCE(MAX(id_commande), 0) + 1 FROM commande"
            )->fetchColumn();

            $requeteCommande = $connexion->prepare(
                "INSERT INTO commande (id_commande, date_commande, id_client)
                 VALUES (?, CURDATE(), ?)"
            );
            $requeteCommande->execute([$prochainId, $idClient]);

            $prochainDetail = (int)$connexion->query(
                "SELECT COALESCE(MAX(id_Details), 0) + 1
                 FROM details_commande"
            )->fetchColumn();

            $requeteDetail = $connexion->prepare(
                "INSERT INTO details_commande
                 (id_Details, `quantité`, prix_unitaire, id_commande, id_produit)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $requeteStock = $connexion->prepare(
                "UPDATE produit
                 SET quantite = quantite - ?
                 WHERE id_produit = ? AND quantite >= ?"
            );

            foreach ($articlesCommande as $article) {
                $quantite = (int)$article["quantite_panier"];
                $idProduit = (int)$article["id_produit"];

                $requeteDetail->execute([
                    $prochainDetail,
                    $quantite,
                    $article["prix"],
                    $prochainId,
                    $idProduit
                ]);

                $requeteStock->execute([
                    $quantite,
                    $idProduit,
                    $quantite
                ]);

                if ($requeteStock->rowCount() !== 1) {
                    throw new RuntimeException(
                        "Le stock a changé. Réessaie ta commande."
                    );
                }

                $prochainDetail++;
            }

            // Supprimer uniquement les articles commandés par ce client
            $requete = $connexion->prepare(
                "DELETE FROM panier WHERE id_client = ?"
            );
            $requete->execute([$idClient]);

            $connexion->commit();

            $_SESSION["derniere_commande"] = [
                "id" => $prochainId,
                "total" => $total
            ];

            header("Location: panier.php?commande=ok");
            exit;

        } catch (Throwable $e) {
            if ($connexion->inTransaction()) {
                $connexion->rollBack();
            }

            $message = "Commande impossible : " . $e->getMessage();
            $typeMessage = "erreur";
        }
    }
}

// Message après validation de commande
if (isset($_GET["commande"]) && $_GET["commande"] === "ok") {
    $derniereCommande = $_SESSION["derniere_commande"] ?? null;

    if ($derniereCommande) {
        $message = "Commande n°" . (int)$derniereCommande["id"]
            . " enregistrée ! Total : "
            . number_format((float)$derniereCommande["total"], 2, ",", " ")
            . " €.";
        $typeMessage = "succes";
    }
}

// Lire les articles enregistrés pour ce client dans MySQL
$requete = $connexion->prepare(
    "SELECT p.id_produit, p.nom_produit, p.prix,
            p.quantite AS stock, pa.quantite AS quantite_panier
     FROM panier pa
     INNER JOIN produit p ON p.id_produit = pa.id_produit
     WHERE pa.id_client = ?
     ORDER BY pa.date_ajout DESC"
);
$requete->execute([$idClient]);
$articles = $requete->fetchAll(PDO::FETCH_ASSOC);

$totalGeneral = 0;

foreach ($articles as &$article) {
    $article["sous_total"] = (float)$article["prix"]
        * (int)$article["quantite_panier"];
    $totalGeneral += $article["sous_total"];
}
unset($article);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier - OnlineStore</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .cart-page {
            width: min(1100px, 92%);
            margin: 50px auto 80px;
        }

        .cart-title {
            margin-bottom: 10px;
            color: #0f172a;
            font-size: 32px;
        }

        .cart-subtitle {
            margin-bottom: 30px;
            color: #64748b;
        }

        .cart-message {
            margin-bottom: 25px;
            padding: 15px 18px;
            border-radius: 10px;
        }

        .cart-message.succes {
            color: #166534;
            background: #dcfce7;
        }

        .cart-message.erreur {
            color: #991b1b;
            background: #fee2e2;
        }

        .cart-table-wrapper {
            overflow-x: auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .cart-table th,
        .cart-table td {
            padding: 18px 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .cart-table th {
            color: #475569;
            background: #f8fafc;
            font-size: 13px;
        }

        .cart-table td {
            color: #334155;
            font-size: 14px;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-quantity input {
            width: 70px;
            padding: 9px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
        }

        .cart-button {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
        }

        .cart-button.update {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .cart-button.delete {
            color: #b91c1c;
            background: #fee2e2;
        }

        .cart-bottom {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .cart-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .cart-summary {
            width: min(100%, 350px);
            padding: 25px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .cart-summary h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
            color: #0f172a;
            font-size: 19px;
            font-weight: 800;
        }

        .cart-summary form button {
            width: 100%;
        }

        .cart-empty {
            padding: 55px 20px;
            text-align: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
        }

        .cart-empty h2 {
            margin-bottom: 10px;
            color: #0f172a;
        }

        .cart-empty p {
            margin-bottom: 22px;
            color: #64748b;
        }

        .cart-back {
            display: inline-block;
            margin-top: 25px;
            color: #2563eb;
            font-weight: 700;
        }

        @media (max-width: 650px) {
            .cart-title {
                font-size: 26px;
            }

            .cart-bottom {
                flex-direction: column;
            }

            .cart-summary {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container header-container">
        <a href="index2.php" class="logo">OnlineStore</a>

        <nav class="navbar">
            <a href="index2.php">Accueil</a>
            <a href="index2.php#produits">Nos produits</a>
            <a href="index2.php#categories">Catégories</a>
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
            <a href="panier.php" class="btn-panier">Mon panier</a>
            <a href="deconnexion.php" class="btn-deconnexion">
                Déconnexion
            </a>
        </div>
    </div>
</header>

<main class="cart-page">
    <h1 class="cart-title">Mon panier</h1>
    <p class="cart-subtitle">
        Retrouve ici les produits que tu souhaites commander.
    </p>

    <?php if ($message !== ""): ?>
        <div class="cart-message <?= htmlspecialchars($typeMessage) ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <?php if (empty($articles)): ?>

        <div class="cart-empty">
            <h2>Ton panier est vide 🛒</h2>
            <p>Découvre notre matériel informatique et ajoute tes produits préférés.</p>
            <a href="index2.php#produits" class="btn-principal">
                Découvrir les produits
            </a>
        </div>

    <?php else: ?>

        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    $article["nom_produit"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <?= number_format(
                                (float)$article["prix"],
                                2,
                                ",",
                                " "
                            ) ?> €
                        </td>

                        <td>
                            <form method="post" action="panier.php"
                                  class="cart-quantity">
                                <input type="hidden" name="action"
                                       value="modifier">
                                <input type="hidden" name="id_produit"
                                       value="<?= (int)$article["id_produit"] ?>">

                                <input type="number" name="quantite"
                                       min="1"
                                       max="<?= (int)$article["quantite"] ?>"
                                       value="<?= (int)$article["quantite_panier"] ?>"
                                       required
                                       aria-label="Quantité">

                                <button class="cart-button update" type="submit">
                                    Modifier
                                </button>
                            </form>
                        </td>

                        <td>
                            <strong>
                                <?= number_format(
                                    (float)$article["sous_total"],
                                    2,
                                    ",",
                                    " "
                                ) ?> €
                            </strong>
                        </td>

                        <td>
                            <form method="post" action="panier.php">
                                <input type="hidden" name="action"
                                       value="supprimer">
                                <input type="hidden" name="id_produit"
                                       value="<?= (int)$article["id_produit"] ?>">

                                <button class="cart-button delete" type="submit">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-bottom">
            <div class="cart-actions">
                <a href="index2.php#produits" class="btn-voir">
                    Continuer mes achats
                </a>

                <form method="post" action="panier.php"
                      onsubmit="return confirm('Veux-tu vraiment vider ton panier ?');">
                    <input type="hidden" name="action" value="vider">
                    <button type="submit" class="cart-button delete">
                        Vider le panier
                    </button>
                </form>
            </div>

            <aside class="cart-summary">
                <h2>Résumé de la commande</h2>

                <div class="cart-total">
                    <span>Total</span>
                    <span>
                        <?= number_format($totalGeneral, 2, ",", " ") ?> €
                    </span>
                </div>

                <p style="margin-bottom: 18px; color: #64748b; font-size: 13px;">
                    Les frais de livraison ne sont pas inclus.
                </p>

                <form method="post" action="panier.php"
                      onsubmit="return confirm('Confirmer cette commande ?');">
                    <input type="hidden" name="action" value="commander">
                    <button type="submit" class="btn-principal">
                        Confirmer ma commande
                    </button>
                </form>
            </aside>
        </div>

    <?php endif; ?>

    <a href="index2.php" class="cart-back">
        ← Retour à la boutique
    </a>
</main>

<footer class="footer">
    <div class="container">
        <p>&copy; <?= date("Y") ?> OnlineStore. Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>
