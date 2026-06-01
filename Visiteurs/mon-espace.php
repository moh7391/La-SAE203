<?php
// Le visiteur retrouve ses inscriptions avec son email, puis peut
// changer de creneau ou annuler. (Pas de mot de passe : version simple.)

session_start();
require_once 'connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $action = $_POST['action'];

    // Se connecter : on cherche le participant par son email.
    if ($action == "connexion") {
        $email = mysqli_real_escape_string($CONNEXION, $_POST['email']);
        $res = mysqli_query($CONNEXION,
            "SELECT id_participant, prenom FROM participant WHERE email = '$email'");
        $participant = mysqli_fetch_assoc($res);

        if ($participant) {
            $_SESSION['participant_id'] = $participant['id_participant'];
            $_SESSION['participant_prenom'] = $participant['prenom'];
        } else {
            $message = "Aucune inscription trouvee pour cet e-mail.";
        }
    }

    // Se deconnecter.
    if ($action == "deconnexion") {
        session_destroy();
        header("Location: mon-espace.php");
        exit;
    }

    // Annuler une inscription.
    if ($action == "annuler" && isset($_SESSION['participant_id'])) {
        $idInscription = (int) $_POST['id_inscription'];
        $idParticipant = (int) $_SESSION['participant_id'];
        // On ajoute "AND id_participant" pour qu'on ne puisse annuler que SES inscriptions.
        mysqli_query($CONNEXION,
            "DELETE FROM inscription
             WHERE id_inscription = $idInscription AND id_participant = $idParticipant");
        $message = "Inscription annulee.";
    }

    // Changer le creneau d'une inscription.
    if ($action == "modifier" && isset($_SESSION['participant_id'])) {
        $idInscription = (int) $_POST['id_inscription'];
        $nouveauCreneau = (int) $_POST['nouveau_creneau'];
        $idParticipant = (int) $_SESSION['participant_id'];

        // On verifie d'abord que le nouveau creneau n'est pas complet.
        $res = mysqli_query($CONNEXION, "SELECT jauge FROM creneau WHERE id_creneau = $nouveauCreneau");
        $creneau = mysqli_fetch_assoc($res);
        $res = mysqli_query($CONNEXION, "SELECT COUNT(*) AS nb FROM inscription WHERE id_creneau = $nouveauCreneau");
        $compte = mysqli_fetch_assoc($res);

        if ($compte['nb'] >= $creneau['jauge']) {
            $message = "Ce creneau est complet.";
        } else {
            mysqli_query($CONNEXION,
                "UPDATE inscription SET id_creneau = $nouveauCreneau
                 WHERE id_inscription = $idInscription AND id_participant = $idParticipant");
            $message = "Creneau modifie.";
        }
    }
}

require_once 'header.php';
?>

  <main>
    <h1>Mon inscription</h1>

    <?php if ($message != "") { ?>
      <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php } ?>

    <?php if (!isset($_SESSION['participant_id'])) { ?>

      <!-- Pas connecte : on demande l'email -->
      <p>Saisissez l'adresse e-mail utilisee lors de votre inscription.</p>
      <form action="mon-espace.php" method="post">
        <input type="hidden" name="action" value="connexion">
        <p>
          <label for="email">Adresse e-mail</label><br>
          <input type="email" id="email" name="email" required>
        </p>
        <p><button type="submit">Retrouver mon inscription</button></p>
      </form>

    <?php } else {

        // On recupere les inscriptions du participant connecte.
        $idParticipant = (int) $_SESSION['participant_id'];
        $mesInscriptions = mysqli_query($CONNEXION,
            "SELECT inscription.id_inscription, creneau.date_creneau, creneau.heure_debut,
                    creneau.heure_fin, salle.nom_salle
             FROM inscription
             JOIN creneau ON creneau.id_creneau = inscription.id_creneau
             JOIN salle ON salle.id_salle = creneau.id_salle
             WHERE inscription.id_participant = $idParticipant
             ORDER BY creneau.date_creneau, creneau.heure_debut");
    ?>

      <p>Bonjour <strong><?php echo htmlspecialchars($_SESSION['participant_prenom']); ?></strong>.</p>
      <form action="mon-espace.php" method="post">
        <input type="hidden" name="action" value="deconnexion">
        <button type="submit">Me deconnecter</button>
      </form>

      <?php if (mysqli_num_rows($mesInscriptions) == 0) { ?>
        <p>Vous n'avez aucune inscription. <a href="inscription.php">S'inscrire</a></p>
      <?php } else {

          while ($ins = mysqli_fetch_assoc($mesInscriptions)) {
              $date = date('d/m/Y', strtotime($ins['date_creneau']));
              $debut = substr($ins['heure_debut'], 0, 5);
              $fin = substr($ins['heure_fin'], 0, 5);
      ?>
        <section>
          <h2><?php echo htmlspecialchars("$date - $debut a $fin - " . $ins['nom_salle']); ?></h2>

          <!-- Changer de creneau -->
          <form action="mon-espace.php" method="post">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id_inscription" value="<?php echo $ins['id_inscription']; ?>">
            <label for="c<?php echo $ins['id_inscription']; ?>">Changer de creneau :</label><br>
            <select id="c<?php echo $ins['id_inscription']; ?>" name="nouveau_creneau" required>
              <option value="">-- Choisir --</option>
              <?php
              // Liste des creneaux disponibles.
              $liste = mysqli_query($CONNEXION,
                  "SELECT creneau.*, salle.nom_salle
                   FROM creneau
                   JOIN salle ON salle.id_salle = creneau.id_salle
                   ORDER BY date_creneau, heure_debut");
              while ($cd = mysqli_fetch_assoc($liste)) {
                  $d = date('d/m/Y', strtotime($cd['date_creneau']));
                  $hd = substr($cd['heure_debut'], 0, 5);
                  $hf = substr($cd['heure_fin'], 0, 5);
                  $texte = "$d - $hd a $hf - " . $cd['nom_salle'];
              ?>
                <option value="<?php echo $cd['id_creneau']; ?>"><?php echo htmlspecialchars($texte); ?></option>
              <?php } ?>
            </select>
            <button type="submit">Modifier</button>
          </form>

          <!-- Annuler -->
          <form action="mon-espace.php" method="post" onsubmit="return confirm('Annuler cette inscription ?');">
            <input type="hidden" name="action" value="annuler">
            <input type="hidden" name="id_inscription" value="<?php echo $ins['id_inscription']; ?>">
            <button type="submit">Annuler</button>
          </form>
        </section>
      <?php }
        }
      } ?>
  </main>

<?php require_once 'footer.php'; ?>
