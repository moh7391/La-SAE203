<?php
// Page visiteur : retrouver, modifier ou annuler une reservation.

require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'reservation';
$page_title = eillusion_page_title('Ma reservation');

$message = '';
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = 'rechercher';

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    $emailSql = mysqli_real_escape_string($CONNEXION, $email);

    if ($email == '') {
        $erreur = 'Merci d entrer votre email.';
    }

    if ($action == 'annuler' && $email != '') {
        $idInscription = (int) $_POST['id_inscription'];

        $sql = "DELETE inscription
                FROM inscription
                JOIN participant ON participant.id_participant = inscription.id_participant
                WHERE inscription.id_inscription = $idInscription
                AND participant.email = '$emailSql'";

        mysqli_query($CONNEXION, $sql);
        $message = 'Inscription annulee.';
    }

    if ($action == 'modifier' && $email != '') {
        $idInscription = (int) $_POST['id_inscription'];
        $nouveauCreneau = (int) $_POST['nouveau_creneau'];
        $creneau = eillusion_get_creneau($CONNEXION, $nouveauCreneau);

        if (!$creneau || $creneau['places_restantes'] <= 0) {
            $erreur = 'Ce creneau est complet.';
        } else {
            $sql = "UPDATE inscription
                    JOIN participant ON participant.id_participant = inscription.id_participant
                    SET inscription.id_creneau = $nouveauCreneau
                    WHERE inscription.id_inscription = $idInscription
                    AND participant.email = '$emailSql'";

            mysqli_query($CONNEXION, $sql);
            $message = 'Creneau modifie.';
        }
    }
}

// On recupere les reservations seulement si un email a ete saisi.
$reservations = array();

if ($email != '') {
    $emailSql = mysqli_real_escape_string($CONNEXION, $email);

    $sql = "SELECT inscription.id_inscription, creneau.id_creneau,
                   creneau.date_creneau, creneau.heure_debut, creneau.heure_fin,
                   salle.nom_salle, participant.prenom, participant.nom
            FROM inscription
            JOIN participant ON participant.id_participant = inscription.id_participant
            JOIN creneau ON creneau.id_creneau = inscription.id_creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            WHERE participant.email = '$emailSql'
            ORDER BY creneau.date_creneau, creneau.heure_debut";

    $resultat = mysqli_query($CONNEXION, $sql);

    while ($reservation = mysqli_fetch_assoc($resultat)) {
        $reservations[] = $reservation;
    }

    if (count($reservations) == 0 && $erreur == '' && $message == '') {
        $erreur = 'Aucune reservation trouvee pour cet email.';
    }
}

$tousLesCreneaux = eillusion_get_creneaux($CONNEXION, 0);

require_once 'header.php';
?>
<main class="reservation-page">
  <section class="container">
    <p class="eyebrow">G&eacute;rer ma r&eacute;servation</p>
    <h1 class="pixel-title page">Modifier ou annuler</h1>
    <p class="lead">Entrez l'adresse e-mail utilis&eacute;e lors de votre inscription pour retrouver vos r&eacute;servations.</p>

    <?php if ($message != '') { ?>
      <div class="success"><?php echo e($message); ?></div>
    <?php } ?>

    <?php if ($erreur != '') { ?>
      <div class="error"><?php echo e($erreur); ?></div>
    <?php } ?>

    <form action="mon-espace.php" method="post" class="panel">
      <input type="hidden" name="action" value="rechercher">

      <div class="field">
        <label for="email">Adresse e-mail</label>
        <input class="input" type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
      </div>

      <div class="field-actions">
        <button type="submit" class="btn">Rechercher</button>
      </div>
    </form>

    <?php foreach ($reservations as $reservation) { ?>
      <article class="reservation-item">
        <h2><?php echo e($reservation['prenom'] . ' ' . $reservation['nom']); ?></h2>

        <p><strong>Salle :</strong> <?php echo e($reservation['nom_salle']); ?></p>

        <p>
          <strong>Date :</strong>
          <?php echo e(eillusion_date_label($reservation['date_creneau'])); ?>
          &agrave;
          <?php echo e(eillusion_heure($reservation['heure_debut'])); ?>
        </p>

        <div class="reservation-actions">
          <form action="mon-espace.php" method="post">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id_inscription" value="<?php echo (int) $reservation['id_inscription']; ?>">
            <input type="hidden" name="email" value="<?php echo e($email); ?>">

            <label for="c<?php echo (int) $reservation['id_inscription']; ?>">Changer de cr&eacute;neau :</label>

            <select class="input" id="c<?php echo (int) $reservation['id_inscription']; ?>" name="nouveau_creneau" required>
              <option value="">-- Choisir --</option>

              <?php foreach ($tousLesCreneaux as $creneau) { ?>
                <?php
                $complet = $creneau['places_restantes'] <= 0;
                $texte = eillusion_date_courte($creneau['date_creneau']);
                $texte = $texte . ' ' . eillusion_heure($creneau['heure_debut']);
                $texte = $texte . ' - ' . $creneau['nom_salle'];

                if ($complet) {
                    $texte = $texte . ' (complet)';
                }
                ?>

                <option value="<?php echo (int) $creneau['id_creneau']; ?>" <?php if ($complet) { echo 'disabled'; } ?>>
                  <?php echo e($texte); ?>
                </option>
              <?php } ?>
            </select>

            <button type="submit" class="btn btn-spaced">Modifier</button>
          </form>

          <form action="mon-espace.php" method="post">
            <input type="hidden" name="action" value="annuler">
            <input type="hidden" name="id_inscription" value="<?php echo (int) $reservation['id_inscription']; ?>">
            <input type="hidden" name="email" value="<?php echo e($email); ?>">

            <button type="submit" class="btn secondary">Annuler cette r&eacute;servation</button>
          </form>
        </div>
      </article>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
