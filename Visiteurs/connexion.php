<?php
// Connexion à la base de données.
// On choisit automatiquement : en local (XAMPP) ou en ligne (mmi-agences).

// On désactive les erreurs automatiques de mysqli pour gérer nous-mêmes
// l'échec de connexion (sinon PHP 8 affiche une page 500).
mysqli_report(MYSQLI_REPORT_OFF);

if ($_SERVER['SERVER_NAME'] == "localhost" || $_SERVER['SERVER_NAME'] == "127.0.0.1") {
    // Sur mon ordinateur (XAMPP)
    $SERVEUR = "127.0.0.1";
    $UTILISATEUR = "root";
    $MOTDEPASSE = "";
    $BASE = "203";
} else {
    // En ligne (serveur mmi-agences)
    $SERVEUR = "192.168.135.113";
    $UTILISATEUR = "morissne";
    $MOTDEPASSE = "Zlatan@@2203"; // <-- remplace par ton mot de passe MySQL
    $BASE = "morissne";
}

$CONNEXION = mysqli_connect($SERVEUR, $UTILISATEUR, $MOTDEPASSE, $BASE);

if (!$CONNEXION) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

mysqli_set_charset($CONNEXION, "utf8mb4");
?>
