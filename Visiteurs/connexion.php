<?php
// Connexion à la base de données.
// On choisit automatiquement : en local (XAMPP) ou en ligne (OVH).

if ($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "127.0.0.1") {
    // Sur mon ordinateur (XAMPP)
    $SERVEUR = "127.0.0.1";
    $UTILISATEUR = "root";
    $MOTDEPASSE = "";
    $BASE = "203";
} else {
    // En ligne (serveur OVH)
    $SERVEUR = "https://mmi-agences.univ-savoie.fr";
    $UTILISATEUR = "jacqluca";
    $MOTDEPASSE = "IoneUééT03122007:/*";
    $BASE = "jacqluca";
}

$CONNEXION = mysqli_connect($SERVEUR, $UTILISATEUR, $MOTDEPASSE, $BASE);

if (!$CONNEXION) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

mysqli_set_charset($CONNEXION, "utf8mb4");
?>
