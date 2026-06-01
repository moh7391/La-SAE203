<?php
// Gestion des creneaux : ajouter, modifier, supprimer, lister.

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $action = $_POST['action'];

    // Supprimer un creneau.
    if ($action == "supprimer") {
        $id = (int) $_POST['id_creneau'];
        // @ : on cache l'erreur si le creneau a des inscriptions (cle etrangere).
        if (@mysqli_query($CONNEXION, "DELETE FROM creneau WHERE id_creneau = $id")) {
            $message = "Creneau supprime.";
        }
        if (mysqli_affected_rows($CONNEXION) == 0) {
            $message = "Suppression impossible : ce creneau a des inscriptions.";
        }
    }

    // Ajouter ou modifier un creneau.
    if ($action == "ajouter" || $action == "modifier") {
        $date = mysqli_real_escape_string($CONNEXION, $_POST['date_creneau']);
        $debut = mysqli_real_escape_string($CONNEXION, $_POST['heure_debut']);
        $fin = mysqli_real_escape_string($CONNEXION, $_POST['heure_fin']);
        $jauge = (int) $_POST['jauge'];
        $idSalle = (int) $_POST['id_salle'];
        $idAdmin = (int) $_SESSION['admin_id'];

        if ($date == "" || $debut == "" || $fin == "" || $jauge == 0 || $idSalle == 0) {
            $message = "Merci de remplir tous les champs.";
        } else if ($action == "ajouter") {
            mysqli_query($CONNEXION,
                "INSERT INTO creneau (date_creneau, heure_debut, heure_fin, jauge, id_admin, id_salle)
                 VALUES ('$date', '$debut', '$fin', $jauge, $idAdmin, $idSalle)");
            $message = "Creneau ajoute.";
        } else {
            $id = (int) $_POST['id_creneau'];
            mysqli_query($CONNEXION,
                "UPDATE creneau
                 SET date_creneau = '$date', heure_debut = '$debut', heure_fin = '$fin',
                     jauge = $jauge, id_salle = $idSalle
                 WHERE id_creneau = $id");
            $message = "Creneau modifie.";
        }
    }
}

// Si on clique sur "Modifier" (creneaux.php?modifier=12), on charge ce creneau.
$creneauEdit = null;
if (isset($_GET['modifier'])) {
    $id = (int) $_GET['modifier'];
    $res = mysqli_query($CONNEXION, "SELECT * FROM creneau WHERE id_creneau = $id");
    $creneauEdit = mysqli_fetch_assoc($res);
}

// Liste des salles (pour le menu deroulant).
$salles = mysqli_query($CONNEXION, "SELECT * FROM salle ORDER BY nom_salle");

// Liste des creneaux a afficher.
$creneaux = mysqli_query($CONNEXION,
    "SELECT creneau.*, salle.nom_salle
     FROM creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     ORDER BY date_creneau, heure_debut");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Gestion des creneaux</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></strong>
       - <a href="accueil.php">Tableau de bord</a> - <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des creneaux</h1>

    <?php if ($message != "") { ?>
      <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php } ?>

    <!-- Formulaire ajouter / modifier -->
    <?php if ($creneauEdit) { ?>
      <h2>Modifier le creneau</h2>
    <?php } else { ?>
      <h2>Ajouter un creneau</h2>
    <?php } ?>

    <form action="creneaux.php" method="post">
      <?php if ($creneauEdit) { ?>
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id_creneau" value="<?php echo $creneauEdit['id_creneau']; ?>">
      <?php } else { ?>
        <input type="hidden" name="action" value="ajouter">
      <?php } ?>

      <p>
        <label for="date_creneau">Date</label><br>
        <input type="date" id="date_creneau" name="date_creneau" required
               value="<?php echo $creneauEdit['date_creneau'] ?? ''; ?>">
      </p>
      <p>
        <label for="heure_debut">Heure de debut</label><br>
        <input type="time" id="heure_debut" name="heure_debut" required
               value="<?php echo $creneauEdit['heure_debut'] ?? ''; ?>">
      </p>
      <p>
        <label for="heure_fin">Heure de fin</label><br>
        <input type="time" id="heure_fin" name="heure_fin" required
               value="<?php echo $creneauEdit['heure_fin'] ?? ''; ?>">
      </p>
      <p>
        <label for="jauge">Nombre de places</label><br>
        <input type="number" id="jauge" name="jauge" min="1" required
               value="<?php echo $creneauEdit['jauge'] ?? '12'; ?>">
      </p>
      <p>
        <label for="id_salle">Salle</label><br>
        <select id="id_salle" name="id_salle" required>
          <option value="">-- Choisir une salle --</option>
          <?php while ($s = mysqli_fetch_assoc($salles)) { ?>
            <option value="<?php echo $s['id_salle']; ?>"
              <?php if ($creneauEdit && $creneauEdit['id_salle'] == $s['id_salle']) echo "selected"; ?>>
              <?php echo htmlspecialchars($s['nom_salle']); ?>
            </option>
          <?php } ?>
        </select>
      </p>
      <p><button type="submit">Enregistrer</button></p>
    </form>

    <!-- Liste des creneaux -->
    <h2>Liste des creneaux</h2>
    <table>
      <tr>
        <th>Date</th>
        <th>Horaire</th>
        <th>Salle</th>
        <th>Places</th>
        <th>Actions</th>
      </tr>
      <?php while ($c = mysqli_fetch_assoc($creneaux)) {
          $date = date('d/m/Y', strtotime($c['date_creneau']));
          $debut = substr($c['heure_debut'], 0, 5);
          $fin = substr($c['heure_fin'], 0, 5);
      ?>
        <tr>
          <td><?php echo htmlspecialchars($date); ?></td>
          <td><?php echo htmlspecialchars($debut . " a " . $fin); ?></td>
          <td><?php echo htmlspecialchars($c['nom_salle']); ?></td>
          <td><?php echo $c['jauge']; ?></td>
          <td>
            <a href="creneaux.php?modifier=<?php echo $c['id_creneau']; ?>">Modifier</a>
            <form action="creneaux.php" method="post" onsubmit="return confirm('Supprimer ce creneau ?');">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id_creneau" value="<?php echo $c['id_creneau']; ?>">
              <button type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </table>
  </main>
</body>
</html>
