<?php
// =====================================================================
//  Page de TEST de la connexion PDO.
//  A supprimer une fois le projet termine (fichier de verification).
// =====================================================================

require_once('connexion.php');

// Si on arrive ici, c'est que la connexion a reussi (sinon le die()
// dans connexion.php aurait deja arrete le script).
echo "<h1>Connexion PDO reussie a la base 203.</h1>";

// --- Petit test concret : on compte les salles et les creneaux ---
$nbSalles   = $pdo->query("SELECT COUNT(*) FROM salle")->fetchColumn();
$nbCreneaux = $pdo->query("SELECT COUNT(*) FROM creneau")->fetchColumn();

echo "<p>Nombre de salles : <strong>$nbSalles</strong></p>";
echo "<p>Nombre de creneaux : <strong>$nbCreneaux</strong></p>";
?>
