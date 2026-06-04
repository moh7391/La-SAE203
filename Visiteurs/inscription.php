<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'inscription';
$page_title = eillusion_page_title('Inscription');

$erreur = '';
$etape = 1;

// Valeurs recues dans l'adresse, par exemple inscription.php?id_salle=1.
$idSalle = 0;
$idCreneau = 0;

if (isset($_GET['id_salle'])) {
    $idSalle = (int) $_GET['id_salle'];
}

if (isset($_GET['id_creneau'])) {
    $idCreneau = (int) $_GET['id_creneau'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'choisir_salle') {
        $idSalle = (int) $_POST['id_salle'];

        if ($idSalle == 0) {
            $erreur = 'Merci de choisir une salle.';
            $etape = 1;
        } else {
            $etape = 2;
        }
    }

    if ($action == 'choisir_creneau') {
        $idSalle = (int) $_POST['id_salle'];
        $idCreneau = (int) $_POST['id_creneau'];

        if ($idCreneau == 0) {
            $erreur = 'Merci de choisir un creneau.';
            $etape = 2;
        } else {
            $etape = 3;
        }
    }

    if ($action == 'confirmer') {
        $idCreneau = (int) $_POST['id_creneau'];

        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);

        $nomSql = mysqli_real_escape_string($CONNEXION, $nom);
        $prenomSql = mysqli_real_escape_string($CONNEXION, $prenom);
        $emailSql = mysqli_real_escape_string($CONNEXION, $email);

        if ($nom == '' || $prenom == '' || $email == '' || $idCreneau == 0) {
            $erreur = 'Merci de remplir tous les champs.';
            $etape = 3;
        } else {
            $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);

            if (!$creneau || $creneau['places_restantes'] <= 0) {
                $erreur = 'Desole, ce creneau est complet.';
                $etape = 2;
            } else {
                // On cherche le participant avec son email.
                $sqlParticipant = "SELECT id_participant
                                   FROM participant
                                   WHERE email = '$emailSql'";
                $resultatParticipant = mysqli_query($CONNEXION, $sqlParticipant);
                $participant = mysqli_fetch_assoc($resultatParticipant);

                if ($participant) {
                    $idParticipant = (int) $participant['id_participant'];
                } else {
                    // Si le participant n'existe pas, on le cree.
                    $sqlAjoutParticipant = "INSERT INTO participant (nom, prenom, email)
                                            VALUES ('$nomSql', '$prenomSql', '$emailSql')";
                    mysqli_query($CONNEXION, $sqlAjoutParticipant);
                    $idParticipant = mysqli_insert_id($CONNEXION);
                }

                // On verifie que la personne n'est pas deja inscrite a ce creneau.
                $sqlDoublon = "SELECT id_inscription
                               FROM inscription
                               WHERE id_creneau = $idCreneau
                               AND id_participant = $idParticipant";
                $resultatDoublon = mysqli_query($CONNEXION, $sqlDoublon);
                $doublon = mysqli_fetch_assoc($resultatDoublon);

                if ($doublon) {
                    $erreur = 'Cette adresse email est deja inscrite a ce creneau.';
                    $etape = 3;
                } else {
                    $dateInscription = date('Y-m-d');

                    $sqlInscription = "INSERT INTO inscription (id_creneau, id_participant, date_inscription)
                                       VALUES ($idCreneau, $idParticipant, '$dateInscription')";
                    mysqli_query($CONNEXION, $sqlInscription);

                    $idInscription = mysqli_insert_id($CONNEXION);

                    // Email de confirmation.
                    $destinataire = $email;
                    $sujet = "Confirmation de votre inscription - E-LLUSION";
                    $jour = eillusion_date_label($creneau['date_creneau']);
                    $heure = eillusion_heure($creneau['heure_debut']);

                    $contenu = "Bonjour $prenom,\n\n";
                    $contenu = $contenu . "Votre inscription a l'exposition E-LLUSION est confirmee.\n\n";
                    $contenu = $contenu . "Salle : " . $creneau['nom_salle'] . "\n";
                    $contenu = $contenu . "Date : $jour\n";
                    $contenu = $contenu . "Heure : $heure\n\n";
                    $contenu = $contenu . "A bientot !\nL'equipe E-LLUSION";

                    $entetes = "From: noreply@e-llusion.fr";
                    mail($destinataire, $sujet, $contenu, $entetes);

                    header("Location: merci.php?id=$idInscription");
                    exit;
                }
            }
        }
    }
}

// Si la page est ouverte avec une salle ou un creneau deja choisi.
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    if ($idCreneau > 0) {
        $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);

        if ($creneau) {
            $idSalle = (int) $creneau['id_salle'];
            $etape = 3;
        }
    }

    if ($idSalle > 0 && $idCreneau == 0) {
        $etape = 2;
    }
}

require_once 'header.php';
?>
<main class="form-page">
  <section class="form-wrap">
    <p class="eyebrow">Inscription</p>
    <h1 class="pixel-title page">R&eacute;servez votre cr&eacute;neau</h1>

    <?php if ($erreur != '') { ?>
      <div class="error"><?php echo e($erreur); ?></div>
    <?php } ?>

    <?php if ($etape == 1) { ?>
      <?php $salles = eillusion_get_salles($CONNEXION); ?>

      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="choisir_salle">

        <h2>1. Choisissez une salle</h2>

        <div class="choice-grid">
          <?php foreach ($salles as $salle) { ?>
            <label class="choice-card">
              <input type="radio" name="id_salle" value="<?php echo (int) $salle['id_salle']; ?>" required>
              <span class="choice-body">
                <strong><?php echo e($salle['nom_salle']); ?></strong>
              </span>
            </label>
          <?php } ?>
        </div>

        <div class="form-actions">
          <a href="index.php" class="btn secondary">Annuler</a>
          <button type="submit" class="btn">Continuer &rarr;</button>
        </div>
      </form>
    <?php } ?>

    <?php if ($etape == 2) { ?>
      <?php
      $salle = eillusion_get_salle($CONNEXION, $idSalle);
      $creneaux = eillusion_get_creneaux($CONNEXION, $idSalle);

      // On range les creneaux par date.
      $jours = array();

      foreach ($creneaux as $creneau) {
          $date = $creneau['date_creneau'];
          $jours[$date][] = $creneau;
      }
      ?>

      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="choisir_creneau">
        <input type="hidden" name="id_salle" value="<?php echo (int) $idSalle; ?>">

        <h2>2. Choisissez un cr&eacute;neau - <?php echo e($salle['nom_salle']); ?></h2>

        <?php if (count($creneaux) == 0) { ?>
          <div class="notice">Aucun cr&eacute;neau disponible.</div>
        <?php } else { ?>
          <?php foreach ($jours as $date => $listeDuJour) { ?>
            <h3><?php echo e(eillusion_date_label($date)); ?></h3>

            <div class="slot-grid">
              <?php foreach ($listeDuJour as $creneau) { ?>
                <?php $complet = $creneau['places_restantes'] <= 0; ?>

                <label class="slot-card <?php if ($complet) { echo 'complet'; } ?>">
                  <input type="radio"
                         name="id_creneau"
                         value="<?php echo (int) $creneau['id_creneau']; ?>"
                         <?php if ($complet) { echo 'disabled'; } else { echo 'required'; } ?>>

                  <span class="slot-body">
                    <strong><?php echo e(eillusion_heure($creneau['heure_debut'])); ?></strong>

                    <?php if ($complet) { ?>
                      <span>Complet</span>
                    <?php } else { ?>
                      <span><?php echo (int) $creneau['places_restantes']; ?> pl.</span>
                    <?php } ?>
                  </span>
                </label>
              <?php } ?>
            </div>
          <?php } ?>
        <?php } ?>

        <div class="form-actions">
          <a href="inscription.php" class="btn secondary">Retour</a>
          <button type="submit" class="btn">Continuer &rarr;</button>
        </div>
      </form>
    <?php } ?>

    <?php if ($etape == 3) { ?>
      <?php $creneau = eillusion_get_creneau($CONNEXION, $idCreneau); ?>

      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="confirmer">
        <input type="hidden" name="id_creneau" value="<?php echo (int) $idCreneau; ?>">

        <h2>3. Vos informations</h2>

        <?php if ($creneau) { ?>
          <div class="notice">
            Cr&eacute;neau choisi :
            <strong>
              <?php echo e(eillusion_date_label($creneau['date_creneau'])); ?>
              &agrave;
              <?php echo e(eillusion_heure($creneau['heure_debut'])); ?>
            </strong>,
            <?php echo e($creneau['nom_salle']); ?>.
          </div>
        <?php } ?>

        <div class="form-grid">
          <div class="field">
            <label for="nom">Nom</label>
            <input class="input" type="text" id="nom" name="nom" required>
          </div>

          <div class="field">
            <label for="prenom">Pr&eacute;nom</label>
            <input class="input" type="text" id="prenom" name="prenom" required>
          </div>

          <div class="field full">
            <label for="email">Email</label>
            <input class="input" type="email" id="email" name="email" required>
          </div>
        </div>

        <div class="form-actions">
          <a href="inscription.php" class="btn secondary">Recommencer</a>
          <button type="submit" class="btn">Confirmer &#10003;</button>
        </div>
      </form>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
