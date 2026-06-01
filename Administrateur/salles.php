<?php
// Gestion des salles : ajouter, modifier, supprimer, lister.

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $action = $_POST['action'];

    // Supprimer une salle.
    if ($action == "supprimer") {
        $id = (int) $_POST['id_salle'];
        @mysqli_query($CONNEXION, "DELETE FROM salle WHERE id_salle = $id");
        if (mysqli_affected_rows($CONNEXION) == 0) {
            $message = "Suppression impossible : des creneaux utilisent cette salle.";
        } else {
            $message = "Salle supprimee.";
        }
    }

    // Ajouter ou modifier une salle.
    if ($action == "ajouter" || $action == "modifier") {
        $nom = mysqli_real_escape_string($CONNEXION, $_POST['nom_salle']);
        $description = mysqli_real_escape_string($CONNEXION, $_POST['description']);

        if ($nom == "") {
            $message = "Le nom de la salle est obligatoire.";
        } else if ($action == "ajouter") {
            mysqli_query($CONNEXION,
                "INSERT INTO salle (nom_salle, description) VALUES ('$nom', '$description')");
            $message = "Salle ajoutee.";
        } else {
            $id = (int) $_POST['id_salle'];
            mysqli_query($CONNEXION,
                "UPDATE salle SET nom_salle = '$nom', description = '$description' WHERE id_salle = $id");
            $message = "Salle modifiee.";
        }
    }
}

// Si on clique sur "Modifier" (salles.php?modifier=3).
$salleEdit = null;
if (isset($_GET['modifier'])) {
    $id = (int) $_GET['modifier'];
    $res = mysqli_query($CONNEXION, "SELECT * FROM salle WHERE id_salle = $id");
    $salleEdit = mysqli_fetch_assoc($res);
}

// Liste des salles.
$salles = mysqli_query($CONNEXION, "SELECT * FROM salle ORDER BY nom_salle");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Gestion des salles</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></strong>
       - <a href="accueil.php">Tableau de bord</a> - <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des salles</h1>

    <?php if ($message != "") { ?>
      <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php } ?>

    <?php if ($salleEdit) { ?>
      <h2>Modifier la salle</h2>
    <?php } else { ?>
      <h2>Ajouter une salle</h2>
    <?php } ?>

    <form action="salles.php" method="post">
      <?php if ($salleEdit) { ?>
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id_salle" value="<?php echo $salleEdit['id_salle']; ?>">
      <?php } else { ?>
        <input type="hidden" name="action" value="ajouter">
      <?php } ?>

      <p>
        <label for="nom_salle">Nom de la salle</label><br>
        <input type="text" id="nom_salle" name="nom_salle" required
               value="<?php echo htmlspecialchars($salleEdit['nom_salle'] ?? ''); ?>">
      </p>
      <p>
        <label for="description">Description</label><br>
        <textarea id="description" name="description" rows="3" cols="40"><?php echo htmlspecialchars($salleEdit['description'] ?? ''); ?></textarea>
      </p>
      <p><button type="submit">Enregistrer</button></p>
    </form>

    <h2>Liste des salles</h2>
    <table>
      <tr>
        <th>Nom</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
      <?php while ($s = mysqli_fetch_assoc($salles)) { ?>
        <tr>
          <td><?php echo htmlspecialchars($s['nom_salle']); ?></td>
          <td><?php echo htmlspecialchars($s['description']); ?></td>
          <td>
            <a href="salles.php?modifier=<?php echo $s['id_salle']; ?>">Modifier</a>
            <form action="salles.php" method="post" onsubmit="return confirm('Supprimer cette salle ?');">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id_salle" value="<?php echo $s['id_salle']; ?>">
              <button type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </table>
  </main>
</body>
</html>
