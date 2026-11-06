
<?php
session_start();
require_once __DIR__ . "/includes/db.php";

$erreur = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $motDePasse = $_POST["mot_de_passe"] ?? "";

    if ($email === "" || $motDePasse === "") {
        $erreur = "Veuillez renseigner votre e-mail et votre mot de passe.";
    } else {
        $requete = $connexion->prepare(
            "SELECT id_Client, nom, prenom, Email, mot_de_passe
             FROM client
             WHERE Email = ?
             LIMIT 1"
        );

        $requete->execute([$email]);
        $client = $requete->fetch(PDO::FETCH_ASSOC);

        if (
            $client &&
            !empty($client["mot_de_passe"]) &&
            password_verify($motDePasse, $client["mot_de_passe"])
        ) {
            session_regenerate_id(true);

            $_SESSION["client"] = [
                "id" => $client["id_Client"],
                "nom" => $client["nom"],
                "prenom" => $client["prenom"],
                "email" => $client["Email"]
            ];

            header("Location: index2.php");
            exit;
        } else {
            $erreur = "E-mail ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | OnlineStore</title>
    <link rel="stylesheet" href="css/compte.css">
</head>
<body class="account-page">

    <main class="account-card">
        <a class="account-logo" href="index.php">Online<span>Store</span></a>

        <h1>Bon retour !</h1>
        <p class="account-subtitle">
            Connectez-vous pour accéder à votre compte.
        </p>

        <?php if (isset($_GET["inscription"]) && $_GET["inscription"] === "ok"): ?>
            <div class="account-alert account-alert-success">
                Votre compte a bien été créé. Vous pouvez vous connecter.
            </div>
        <?php endif; ?>

        <?php if ($erreur !== ""): ?>
            <div class="account-alert account-alert-error">
                <?= htmlspecialchars($erreur, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="connexion.php">
            <div class="account-field">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email"
                    maxlength="100" autocomplete="email" required
                    value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>">
            </div>

            <div class="account-field">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe"
                    name="mot_de_passe" autocomplete="current-password" required>
            </div>

            <button class="account-submit" type="submit">
                Se connecter
            </button>
        </form>

        <p class="account-switch">
            Vous n'avez pas encore de compte ?
            <a href="inscription.php">Créer un compte</a>
        </p>

        <a class="account-back" href="index.php">
            ← Retour à l'accueil
        </a>
    </main>

</body>
</html>
