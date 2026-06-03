<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'inscription';
$page_title = eillusion_page_title('Inscription');
$erreur = '';
$etape = 1;

// Salle et creneau eventuellement deja choisis (depuis un lien).
$idSalle = isset($_GET['id_salle']) ? (int) $_GET['id_salle'] : 0;
$idCreneau = isset($_GET['id_creneau']) ? (int) $_GET['id_creneau'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Etape 1 : choix de la salle
    if ($action === 'choisir_salle') {
        $idSalle = (int) $_POST['id_salle'];
        if ($idSalle == 0) { $erreur = 'Merci de choisir une salle.'; $etape = 1; }
        else { $etape = 2; }
    }

    // Etape 2 : choix du creneau
    if ($action === 'choisir_creneau') {
        $idSalle = (int) $_POST['id_salle'];
        $idCreneau = (int) $_POST['id_creneau'];
        if ($idCreneau == 0) { $erreur = 'Merci de choisir un créneau.'; $etape = 2; }
        else { $etape = 3; }
    }

    // Etape 3 : informations et enregistrement
    if ($action === 'confirmer') {
        $idCreneau = (int) $_POST['id_creneau'];
        $nom = mysqli_real_escape_string($CONNEXION, trim($_POST['nom']));
        $prenom = mysqli_real_escape_string($CONNEXION, trim($_POST['prenom']));
        $email = mysqli_real_escape_string($CONNEXION, trim($_POST['email']));

        if ($nom == '' || $prenom == '' || $email == '' || $idCreneau == 0) {
            $erreur = 'Merci de remplir tous les champs.'; $etape = 3;
        } else {
            $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
            if (!$creneau || $creneau['places_restantes'] <= 0) {
                $erreur = 'Désolé, ce créneau est complet.'; $etape = 2;
            } else {
                // Participant : on le retrouve par email, sinon on le cree.
                $res = mysqli_query($CONNEXION, "SELECT id_participant FROM participant WHERE email = '$email'");
                $participant = mysqli_fetch_assoc($res);
                if ($participant) {
                    $idParticipant = (int) $participant['id_participant'];
                } else {
                    mysqli_query($CONNEXION,
                        "INSERT INTO participant (nom, prenom, email) VALUES ('$nom', '$prenom', '$email')");
                    $idParticipant = mysqli_insert_id($CONNEXION);
                }

                // Deja inscrit a ce creneau ?
                $res = mysqli_query($CONNEXION,
                    "SELECT id_inscription FROM inscription
                     WHERE id_creneau = $idCreneau AND id_participant = $idParticipant");
                if (mysqli_fetch_assoc($res)) {
                    $erreur = 'Cette adresse e-mail est déjà inscrite à ce créneau.'; $etape = 3;
                } else {
                    $date = date('Y-m-d');
                    mysqli_query($CONNEXION,
                        "INSERT INTO inscription (id_creneau, id_participant, date_inscription)
                         VALUES ($idCreneau, $idParticipant, '$date')");
                    $idInscription = mysqli_insert_id($CONNEXION);
                    header("Location: merci.php?id=$idInscription");
                    exit;
                }
            }
        }
    }
}

// Si on arrive par un lien direct (salle ou creneau deja choisi), on saute des etapes.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($idCreneau > 0) {
        $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
        if ($creneau) { $idSalle = (int) $creneau['id_salle']; $etape = 3; }
    } elseif ($idSalle > 0) {
        $etape = 2;
    }
}

require_once 'header.php';
?>
<main class="form-page">
  <section class="form-wrap">
    <p class="eyebrow">Inscription</p>
    <h1 class="pixel-title page">Réservez votre créneau</h1>

    <?php if ($erreur != '') { ?>
      <div class="error"><?php echo e($erreur); ?></div>
    <?php } ?>

    <?php if ($etape == 1) {
        $salles = eillusion_get_salles($CONNEXION);
    ?>
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
          <button type="submit" class="btn">Continuer →</button>
        </div>
      </form>
    <?php } ?>

    <?php if ($etape == 2) {
        $salle = eillusion_get_salle($CONNEXION, $idSalle);
        $creneaux = eillusion_get_creneaux($CONNEXION, $idSalle);

        // On regroupe les creneaux par jour.
        $jours = array();
        foreach ($creneaux as $c) {
            $jours[$c['date_creneau']][] = $c;
        }
    ?>
      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="choisir_creneau">
        <input type="hidden" name="id_salle" value="<?php echo (int) $idSalle; ?>">
        <h2>2. Choisissez un créneau — <?php echo e($salle['nom_salle']); ?></h2>

        <?php if (count($creneaux) == 0) { ?>
          <div class="notice">Aucun créneau disponible.</div>
        <?php } else { ?>
          <?php foreach ($jours as $date => $listeDuJour) { ?>
            <h3><?php echo e(eillusion_date_label($date)); ?></h3>
            <div class="slot-grid">
              <?php foreach ($listeDuJour as $c) {
                  $complet = $c['places_restantes'] <= 0;
              ?>
                <label class="slot-card <?php echo $complet ? 'complet' : ''; ?>">
                  <input type="radio" name="id_creneau" value="<?php echo (int) $c['id_creneau']; ?>" <?php echo $complet ? 'disabled' : 'required'; ?>>
                  <span class="slot-body">
                    <strong><?php echo e(eillusion_heure($c['heure_debut'])); ?></strong>
                    <span><?php echo $complet ? 'Complet' : ((int) $c['places_restantes'] . ' pl.'); ?></span>
                  </span>
                </label>
              <?php } ?>
            </div>
          <?php } ?>
        <?php } ?>
        <div class="form-actions">
          <a href="inscription.php" class="btn secondary">Retour</a>
          <button type="submit" class="btn">Continuer →</button>
        </div>
      </form>
    <?php } ?>

    <?php if ($etape == 3) {
        $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
    ?>
      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="confirmer">
        <input type="hidden" name="id_creneau" value="<?php echo (int) $idCreneau; ?>">
        <h2>3. Vos informations</h2>

        <?php if ($creneau) { ?>
          <div class="notice">
            Créneau choisi : <strong><?php echo e(eillusion_date_label($creneau['date_creneau'])); ?> à <?php echo e(eillusion_heure($creneau['heure_debut'])); ?></strong>, <?php echo e($creneau['nom_salle']); ?>.
          </div>
        <?php } ?>

        <div class="form-grid">
          <div class="field">
            <label for="nom">Nom</label>
            <input class="input" type="text" id="nom" name="nom" required>
          </div>
          <div class="field">
            <label for="prenom">Prénom</label>
            <input class="input" type="text" id="prenom" name="prenom" required>
          </div>
          <div class="field full">
            <label for="email">Email</label>
            <input class="input" type="email" id="email" name="email" required>
          </div>
        </div>

        <div class="form-actions">
          <a href="inscription.php" class="btn secondary">Recommencer</a>
          <button type="submit" class="btn">Confirmer ✓</button>
        </div>
      </form>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
