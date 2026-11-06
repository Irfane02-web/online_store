
<?php
session_start();
require_once __DIR__ . "/includes/db.php";

$erreur = "";
$nom = "";
$prenom = "";
$email = "";
$adresse = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $adresse = trim($_POST["adresse"] ?? "");
    $motDePasse = $_POST["mot_de_passe"] ?? "";
    $confirmation = $_POST["confirmation"] ?? "";

    if (
        $nom === "" || $prenom === "" || $email === "" ||
        $adresse === "" || $motDePasse === "" || $confirmation === ""
    ) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (mb_strlen($nom) > 255) {
        $erreur = "Le nom ne doit pas dépasser 255 caractères.";
    } elseif (mb_strlen($prenom) > 100) {
        $erreur = "Le prénom ne doit pas dépasser 100 caractères.";
    } elseif (mb_strlen($adresse) > 255) {
        $erreur = "L'adresse ne doit pas dépasser 255 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse e-mail n'est pas valide.";
    } elseif (strlen($email) > 100) {
        $erreur = "L'adresse e-mail est trop longue.";
    } elseif (strlen($motDePasse) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($motDePasse !== $confirmation) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $connexion->beginTransaction();

            // Vérifier si l'adresse e-mail est déjà utilisée
            $verification = $connexion->prepare(
                "SELECT id_Client FROM client WHERE Email = ?"
            );
            $verification->execute([$email]);

            if ($verification->fetch()) {
                $connexion->rollBack();
                $erreur = "Un compte existe déjà avec cette adresse e-mail.";
            } else {
                // Identifiant provisoire car id_Client n'est pas AUTO_INCREMENT
                $maxId = $connexion->query(
                    "SELECT COALESCE(MAX(id_Client), 0) FROM client"
                )->fetchColumn();

                $nouvelId = (int)$maxId + 1;

                if ($nouvelId > 255) {
                    $connexion->rollBack();
                    $erreur = "La limite actuelle des identifiants clients est atteinte. Contactez l'administrateur.";
                } else {
                    $motDePasseHache = password_hash(
                        $motDePasse,
                        PASSWORD_DEFAULT
                    );

                    $insertion = $connexion->prepare(
                        "INSERT INTO client
                        (id_Client, nom, prenom, Email, adresse, mot_de_passe)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    $insertion->execute([
                        $nouvelId,
                        $nom,
                        $prenom,
                        $email,
                        $adresse,
                        $motDePasseHache
                    ]);

                    $connexion->commit();

                    header("Location: connexion.php?inscription=ok");
                    exit;
                }
            }
        } catch (PDOException $e) {
            if ($connexion->inTransaction()) {
                $connexion->rollBack();
            }

            // Une contrainte UNIQUE sur Email protège aussi contre les doublons.
            if ($e->getCode() === "23000") {
                $erreur = "Cet e-mail ou cet identifiant est déjà utilisé.";
            } else {
                error_log($e->getMessage());
                $erreur = "Une erreur est survenue. Veuillez réessayer.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte | OnlineStore</title>
    <link rel="stylesheet" href="css/compte.css">
</head>
<body class="account-page">

    <main class="account-card">
        <a class="account-logo" href="index.php">Online<span>Store</span></a>

        <h1>Créer un compte</h1>
        <p class="account-subtitle">
            Rejoignez OnlineStore et découvrez nos produits informatiques.
        </p>

        <?php if ($erreur !== ""): ?>
            <div class="account-alert account-alert-error">
                <?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="inscription.php">
            <div class="account-field">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom"
                    maxlength="25" autocomplete="family-name" required
                    value="<?= htmlspecialchars($nom, ENT_QUOTES, "UTF-8") ?>">
            </div>

            <div class="account-field">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                    maxlength="100" autocomplete="given-name" required
                    value="<?= htmlspecialchars($prenom, ENT_QUOTES, "UTF-8") ?>">
            </div>

            <div class="account-field">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                    maxlength="100" autocomplete="email" required
                    value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>">
            </div>

            <div class="account-field">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse"
                    maxlength="255" autocomplete="street-address" required
                    value="<?= htmlspecialchars($adresse, ENT_QUOTES, "UTF-8") ?>">
            </div>

            <div class="account-field">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                    minlength="8" autocomplete="new-password" required>
                <small>8 caractères minimum.</small>
            </div>

            <div class="account-field">
                <label for="confirmation">Confirmer le mot de passe</label>
                <input type="password" id="confirmation" name="confirmation"
                    minlength="8" autocomplete="new-password" required>
            </div>

            <button class="account-submit" type="submit">
                Créer mon compte
            </button>
        </form>

        <p class="account-switch">
            Déjà client ?
            <a href="connexion.php">Se connecter</a>
        </p>

        <a class="account-back" href="index.php">
            ← Retour à l'accueil
        </a>
    </main>

</body>
</html>
