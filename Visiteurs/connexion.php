<?php
// Connexion à la base de données

$SERVEUR = "127.0.0.1";
$UTILISATEUR = "root";
$MOTDEPASSE = "";
$BASE = "203";

$CONNEXION = mysqli_connect($SERVEUR, $UTILISATEUR, $MOTDEPASSE, $BASE);

if (!$CONNEXION) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

mysqli_set_charset($CONNEXION, "utf8mb4");
?>