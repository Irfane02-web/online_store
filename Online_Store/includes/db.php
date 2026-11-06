<?php
$hote = "localhost";
$port = "3306"; // Port MySQL courant sur MAMP pour Mac
$base = "online_store";
$utilisateur = "root";
$mot_de_passe = "root";

try {
    $connexion = new PDO(
        "mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4",
        $utilisateur,
        $mot_de_passe
    );

    $connexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}