<?php
require_once('connexion.php');

$requete = mysqli_query($CONNEXION, "SELECT * FROM salle;");

if (!$requete) {
    die("Erreur SQL : " . mysqli_error($CONNEXION));
}

echo "<h1>Liste des salles</h1>";

while ($salle = mysqli_fetch_assoc($requete)) {
    echo $salle['nom_salle'] . "<br>";
}
?>