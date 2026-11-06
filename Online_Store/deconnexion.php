
<?php
session_start();

// Vider les informations de connexion
$_SESSION = [];

// Détruire la session
session_destroy();

// Retourner à la page de connexion
header("Location: connexion.php");
exit;
?>
