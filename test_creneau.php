<?php
require_once('connexion.php');

$requete = mysqli_query($CONNEXION, "
    SELECT 
        creneau.id_creneau,
        creneau.date_creneau,
        creneau.heure_debut,
        creneau.heure_fin,
        creneau.jauge,
        salle.nom_salle
    FROM creneau
    INNER JOIN salle ON creneau.id_salle = salle.id_salle
    ORDER BY creneau.date_creneau, creneau.heure_debut, salle.nom_salle;
");

if (!$requete) {
    die("Erreur SQL : " . mysqli_error($CONNEXION));
}

echo "<h1>Liste des créneaux</h1>";

while ($creneau = mysqli_fetch_assoc($requete)) {
    echo "Salle : " . $creneau['nom_salle'] . " | ";
    echo "Date : " . $creneau['date_creneau'] . " | ";
    echo "Heure : " . substr($creneau['heure_debut'], 0, 5) . " - " . substr($creneau['heure_fin'], 0, 5) . " | ";
    echo "Jauge : " . $creneau['jauge'];
    echo "<br>";
}
?>