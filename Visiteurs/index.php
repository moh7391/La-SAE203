<?php
// Page d'accueil : liste des creneaux avec le nombre de places restantes.

require_once 'connexion.php';

// On recupere tous les creneaux avec le nom de leur salle.
$creneaux = mysqli_query($CONNEXION,
    "SELECT creneau.*, salle.nom_salle
     FROM creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     ORDER BY date_creneau, heure_debut");

require_once 'header.php';
?>

  <main>
    <h1>Creneaux d'inscription a l'exposition</h1>
    <p>Choisissez un creneau puis cliquez sur "S'inscrire".</p>

    <table>
      <caption>Liste des creneaux</caption>
      <tr>
        <th>Date</th>
        <th>Horaire</th>
        <th>Salle</th>
        <th>Places restantes</th>
        <th>Action</th>
      </tr>

      <?php
      // Pour chaque creneau, on compte ses inscrits pour calculer les places restantes.
      while ($c = mysqli_fetch_assoc($creneaux)) {

          $res = mysqli_query($CONNEXION,
              "SELECT COUNT(*) AS nb FROM inscription WHERE id_creneau = " . $c['id_creneau']);
          $compte = mysqli_fetch_assoc($res);

          $places = $c['jauge'] - $compte['nb'];

          // On met la date et les heures dans un format lisible.
          $date = date('d/m/Y', strtotime($c['date_creneau']));
          $debut = substr($c['heure_debut'], 0, 5);
          $fin = substr($c['heure_fin'], 0, 5);
      ?>
        <tr>
          <td><?php echo htmlspecialchars($date); ?></td>
          <td><?php echo htmlspecialchars($debut . " a " . $fin); ?></td>
          <td><?php echo htmlspecialchars($c['nom_salle']); ?></td>
          <td><?php echo $places; ?> / <?php echo $c['jauge']; ?></td>
          <td>
            <?php if ($places > 0) { ?>
              <a href="inscription.php?id_creneau=<?php echo $c['id_creneau']; ?>">S'inscrire</a>
            <?php } else { ?>
              Complet
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </table>
  </main>

<?php require_once 'footer.php'; ?>
