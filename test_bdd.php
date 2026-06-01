<?php
// =====================================================================
//  test_bdd.php  -  Page de TEST (affiche le contenu de la base 203).
//  But : verifier que les tables sont bien peuplees.
//  A SUPPRIMER une fois le projet termine (ce n'est pas une page du site).
// =====================================================================

require_once('connexion.php');

// Liste des tables que l'on veut afficher.
$tables = ['administrateur', 'salle', 'element_expo', 'creneau', 'participant', 'inscription', 'contenir'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Test BDD - base 203</title>
    <style>
        body { font-family: sans-serif; margin: 1rem; }
        h2 { margin-top: 2rem; color: #1a3c5e; }
        table { border-collapse: collapse; margin-top: .5rem; }
        th, td { border: 1px solid #999; padding: 4px 8px; text-align: left; }
        th { background: #e8eef4; }
        caption { font-weight: bold; margin-bottom: .3rem; }
    </style>
</head>
<body>
    <h1>Contenu de la base de données « 203 »</h1>

<?php
// Pour chaque table, on lit toutes les lignes et on les affiche dans un tableau HTML.
foreach ($tables as $table) {

    // Requete : on recupere tout le contenu de la table.
    // (Nom de table interne, pas une donnee utilisateur -> pas de risque d'injection.)
    $resultat = mysqli_query($CONNEXION, "SELECT * FROM $table");

    // Nombre de lignes trouvees.
    $nb = mysqli_num_rows($resultat);

    echo "<h2>Table « $table » — $nb ligne(s)</h2>";

    if ($nb === 0) {
        echo "<p>(table vide)</p>";
        continue; // on passe a la table suivante
    }

    echo "<table>";

    // --- En-tetes : on prend les noms des colonnes a partir de la 1re ligne ---
    $premiereLigne = mysqli_fetch_assoc($resultat);
    echo "<tr>";
    foreach (array_keys($premiereLigne) as $colonne) {
        echo "<th>" . htmlspecialchars($colonne) . "</th>";
    }
    echo "</tr>";

    // --- On affiche la 1re ligne deja lue ---
    echo "<tr>";
    foreach ($premiereLigne as $valeur) {
        echo "<td>" . htmlspecialchars($valeur ?? '') . "</td>";
    }
    echo "</tr>";

    // --- Puis toutes les lignes suivantes ---
    while ($ligne = mysqli_fetch_assoc($resultat)) {
        echo "<tr>";
        foreach ($ligne as $valeur) {
            echo "<td>" . htmlspecialchars($valeur ?? '') . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}
?>
</body>
</html>
