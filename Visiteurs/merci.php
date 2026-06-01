<?php
// Page de confirmation affichee apres une inscription reussie.

require_once 'connexion.php';

// On recupere le numero de l'inscription dans l'adresse (merci.php?id=...).
$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

// On va chercher les details de cette inscription.
$res = mysqli_query($CONNEXION,
    "SELECT participant.nom, participant.prenom, participant.email,
            creneau.date_creneau, creneau.heure_debut, creneau.heure_fin,
            salle.nom_salle
     FROM inscription
     JOIN participant ON participant.id_participant = inscription.id_participant
     JOIN creneau ON creneau.id_creneau = inscription.id_creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     WHERE inscription.id_inscription = $id");
$info = mysqli_fetch_assoc($res);

require_once 'header.php';
?>

  <main>
    <h1>Confirmation d'inscription</h1>

    <?php if (!$info) { ?>
      <p>Inscription introuvable.</p>
      <p><a href="inscription.php">Retour au formulaire</a></p>
    <?php } else {
        $date = date('d/m/Y', strtotime($info['date_creneau']));
        $debut = substr($info['heure_debut'], 0, 5);
        $fin = substr($info['heure_fin'], 0, 5);
    ?>
      <p>Merci <strong><?php echo htmlspecialchars($info['prenom'] . " " . $info['nom']); ?></strong>,
         votre inscription est enregistree.</p>

      <h2>Recapitulatif</h2>
      <ul>
        <li>Date : <?php echo htmlspecialchars($date); ?></li>
        <li>Horaire : <?php echo htmlspecialchars($debut . " a " . $fin); ?></li>
        <li>Salle : <?php echo htmlspecialchars($info['nom_salle']); ?></li>
        <li>E-mail : <?php echo htmlspecialchars($info['email']); ?></li>
      </ul>

      <p><a href="index.php">Retour a l'accueil</a></p>
    <?php } ?>
  </main>

<?php require_once 'footer.php'; ?>
