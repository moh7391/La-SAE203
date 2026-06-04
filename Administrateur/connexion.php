<?php
// Connexion a la base de donnees.
// Le site choisit les identifiants locaux si on est sur XAMPP.

mysqli_report(MYSQLI_REPORT_OFF);

$serveurActuel = $_SERVER['SERVER_NAME'];

if ($serveurActuel == 'localhost' || $serveurActuel == '127.0.0.1') {
    $SERVEUR = '127.0.0.1';
    $UTILISATEUR = 'root';
    $MOTDEPASSE = '';
    $BASE = '203';
} else {
    $SERVEUR = '192.168.135.113';
    $UTILISATEUR = 'morissne';
    $MOTDEPASSE = 'Zlatan@@2203';
    $BASE = 'morissne';
}

$CONNEXION = mysqli_connect($SERVEUR, $UTILISATEUR, $MOTDEPASSE, $BASE);

if (!$CONNEXION) {
    die('Erreur de connexion a la base de donnees : ' . mysqli_connect_error());
}

mysqli_set_charset($CONNEXION, 'utf8mb4');
?>
