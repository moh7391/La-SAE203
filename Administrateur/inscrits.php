<?php
// Liste des inscrits, creneau par creneau. On peut annuler une inscription.

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

// Annuler une inscription.
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = (int) $_POST['id_inscription'];
    mysqli_query($CONNEXION, "DELETE FROM inscription WHERE id_inscription = $id");
    $message = "Inscription annulee.";
}

// On recupere tous les creneaux.
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
  <title>Inscrits par creneau</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></strong>
       - <a href="accueil.php">Tableau de bord</a> - <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Inscrits par creneau</h1>

    <?php if ($message != "") { ?>
      <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php } ?>

    <?php
    // Pour chaque creneau, on affiche la liste de ses inscrits.
    while ($c = mysqli_fetch_assoc($creneaux)) {
        $date = date('d/m/Y', strtotime($c['date_creneau']));
        $debut = substr($c['heure_debut'], 0, 5);
        $fin = substr($c['heure_fin'], 0, 5);

        // Les inscrits de ce creneau.
        $inscrits = mysqli_query($CONNEXION,
            "SELECT inscription.id_inscription, participant.nom, participant.prenom,
                    participant.email, participant.telephone
             FROM inscription
             JOIN participant ON participant.id_participant = inscription.id_participant
             WHERE inscription.id_creneau = " . $c['id_creneau'] . "
             ORDER BY participant.nom");
        $nb = mysqli_num_rows($inscrits);
    ?>
      <section>
        <h2><?php echo htmlspecialchars("$date - $debut a $fin - " . $c['nom_salle']); ?></h2>
        <p><?php echo $nb; ?> inscrit(s) sur <?php echo $c['jauge']; ?> places.</p>

        <?php if ($nb == 0) { ?>
          <p>Aucun inscrit.</p>
        <?php } else { ?>
          <table>
            <tr>
              <th>Nom</th>
              <th>Prenom</th>
              <th>E-mail</th>
              <th>Telephone</th>
              <th>Action</th>
            </tr>
            <?php while ($i = mysqli_fetch_assoc($inscrits)) { ?>
              <tr>
                <td><?php echo htmlspecialchars($i['nom']); ?></td>
                <td><?php echo htmlspecialchars($i['prenom']); ?></td>
                <td><?php echo htmlspecialchars($i['email']); ?></td>
                <td><?php echo htmlspecialchars($i['telephone']); ?></td>
                <td>
                  <form action="inscrits.php" method="post" onsubmit="return confirm('Annuler ?');">
                    <input type="hidden" name="id_inscription" value="<?php echo $i['id_inscription']; ?>">
                    <button type="submit">Annuler</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </table>
        <?php } ?>
      </section>
    <?php } ?>
  </main>
</body>
</html>
