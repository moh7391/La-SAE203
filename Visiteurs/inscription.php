<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'inscription';
$page_title = eillusion_page_title('Inscription');
$erreur = '';
$step = 1;
$salleCode = isset($_GET['salle']) ? $_GET['salle'] : '';
$idCreneau = isset($_GET['id_creneau']) ? (int) $_GET['id_creneau'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'choisir_salle') {
        $salleCode = isset($_POST['salle']) ? $_POST['salle'] : '';
        if ($salleCode === '') {
            $erreur = 'Merci de choisir une salle.';
            $step = 1;
        } else {
            $step = 2;
        }
    }

    if ($action === 'choisir_creneau') {
        $salleCode = isset($_POST['salle']) ? $_POST['salle'] : '';
        $idCreneau = isset($_POST['id_creneau']) ? (int) $_POST['id_creneau'] : 0;
        if ($idCreneau <= 0) {
            $erreur = 'Merci de choisir un créneau.';
            $step = 2;
        } else {
            $step = 3;
        }
    }

    if ($action === 'confirmer') {
        $idCreneau = isset($_POST['id_creneau']) ? (int) $_POST['id_creneau'] : 0;
        $salleCode = isset($_POST['salle']) ? $_POST['salle'] : '';

        $nom = mysqli_real_escape_string($CONNEXION, trim($_POST['nom'] ?? ''));
        $prenom = mysqli_real_escape_string($CONNEXION, trim($_POST['prenom'] ?? ''));
        $email = mysqli_real_escape_string($CONNEXION, trim($_POST['email'] ?? ''));
        $telephone = mysqli_real_escape_string($CONNEXION, trim($_POST['telephone'] ?? ''));

        if ($nom === '' || $prenom === '' || $email === '' || $idCreneau <= 0) {
            $erreur = 'Merci de remplir tous les champs obligatoires.';
            $step = 3;
        } else {
            $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
            if (!$creneau) {
                $erreur = 'Créneau introuvable.';
                $step = 2;
            } elseif ((int) $creneau['places_restantes'] <= 0) {
                $erreur = 'Désolé, ce créneau est complet.';
                $step = 2;
            } else {
                $res = mysqli_query($CONNEXION, "SELECT id_participant FROM participant WHERE email = '$email'");
                $participant = mysqli_fetch_assoc($res);

                if ($participant) {
                    $idParticipant = (int) $participant['id_participant'];
                } else {
                    mysqli_query($CONNEXION,
                        "INSERT INTO participant (nom, prenom, email, telephone)
                         VALUES ('$nom', '$prenom', '$email', '$telephone')");
                    $idParticipant = mysqli_insert_id($CONNEXION);
                }

                $res = mysqli_query($CONNEXION,
                    "SELECT id_inscription FROM inscription
                     WHERE id_creneau = $idCreneau AND id_participant = $idParticipant");
                $deja = mysqli_fetch_assoc($res);

                if ($deja) {
                    $erreur = 'Cette adresse e-mail est déjà inscrite à ce créneau.';
                    $step = 3;
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($idCreneau > 0) {
        $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
        if ($creneau) {
            $salleCode = eillusion_code_from_db_salle($CONNEXION, $creneau['id_salle']);
            $step = 3;
        }
    } elseif ($salleCode !== '') {
        $step = 2;
    }
}

$salles = eillusion_salles();
require_once 'header.php';
?>
<main class="form-page">
  <section class="form-wrap">
    <p class="eyebrow">Inscription</p>
    <h1 class="pixel-title page">Réservez votre<br>créneau</h1>

    <div class="steps" aria-label="Étapes d'inscription">
      <span class="step-dot <?php echo $step >= 1 ? 'active' : ''; ?>"><?php echo $step > 1 ? '✓' : '1'; ?></span>
      <span class="step-line"></span>
      <span class="step-dot <?php echo $step >= 2 ? 'active' : ''; ?>"><?php echo $step > 2 ? '✓' : '2'; ?></span>
      <span class="step-line"></span>
      <span class="step-dot <?php echo $step >= 3 ? 'active' : ''; ?>">3</span>
    </div>

    <?php if ($erreur !== '') { ?>
      <div class="error"><?php echo e($erreur); ?></div>
    <?php } ?>

    <?php if ($step === 1) { ?>
      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="choisir_salle">
        <h2>1. Choisissez une salle</h2>
        <div class="choice-grid">
          <?php foreach ($salles as $salle) { ?>
            <label class="choice-card">
              <input type="radio" name="salle" value="<?php echo e($salle['code']); ?>" required>
              <span class="choice-body">
                <span class="room-number"><?php echo e($salle['code']); ?></span>
                <strong><?php echo e($salle['nom_long']); ?></strong>
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

    <?php if ($step === 2) {
        $dbSalleId = eillusion_salle_db_id($CONNEXION, $salleCode);
        $creneaux = eillusion_get_creneaux($CONNEXION, $dbSalleId);
        if (count($creneaux) === 0) {
            $creneaux = eillusion_get_creneaux($CONNEXION);
        }
        $groupes = array();
        foreach ($creneaux as $c) {
            $groupes[$c['date_creneau']][] = $c;
        }
    ?>
      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="choisir_creneau">
        <input type="hidden" name="salle" value="<?php echo e($salleCode); ?>">
        <h2>2. Date, créneau et nombre de personnes</h2>

        <?php if (count($creneaux) === 0) { ?>
          <div class="notice">Aucun créneau n'est disponible pour le moment.</div>
        <?php } else { ?>
          <div class="date-pills">
            <?php foreach (array_keys($groupes) as $dateSql) { ?>
              <span class="date-pill"><?php echo e(eillusion_date_label($dateSql)); ?></span>
            <?php } ?>
          </div>

          <?php foreach ($groupes as $dateSql => $liste) { ?>
            <h3>Créneaux du <?php echo e(eillusion_date_label($dateSql)); ?> <small>(durée 30 min)</small></h3>
            <div class="slot-grid">
              <?php foreach ($liste as $c) {
                  $places = (int) $c['places_restantes'];
                  $disabled = $places <= 0;
              ?>
                <label class="slot-card <?php echo $disabled ? 'complet' : ''; ?>">
                  <input type="radio" name="id_creneau" value="<?php echo (int) $c['id_creneau']; ?>" <?php echo $disabled ? 'disabled' : 'required'; ?>>
                  <span class="slot-body">
                    <strong><?php echo e(eillusion_heure($c['heure_debut'])); ?></strong>
                    <span><?php echo $places > 0 ? e($places . ' pl.') : 'Complet'; ?></span>
                  </span>
                </label>
              <?php } ?>
            </div>
          <?php } ?>
        <?php } ?>

        <div class="form-actions">
          <a href="inscription.php" class="btn secondary">Retour</a>
          <button type="submit" class="btn" <?php echo count($creneaux) === 0 ? 'disabled' : ''; ?>>Continuer →</button>
        </div>
      </form>
    <?php } ?>

    <?php if ($step === 3) {
        $creneau = eillusion_get_creneau($CONNEXION, $idCreneau);
    ?>
      <form action="inscription.php" method="post">
        <input type="hidden" name="action" value="confirmer">
        <input type="hidden" name="salle" value="<?php echo e($salleCode); ?>">
        <input type="hidden" name="id_creneau" value="<?php echo (int) $idCreneau; ?>">
        <input type="hidden" name="telephone" value="">
        <h2>3. Vos informations</h2>

        <?php if ($creneau) { ?>
          <div class="notice">
            Créneau choisi : <strong><?php echo e(eillusion_date_label($creneau['date_creneau'])); ?> à <?php echo e(eillusion_heure($creneau['heure_debut'])); ?></strong>, salle <?php echo e($creneau['nom_salle']); ?>.
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
          <div class="field full">
            <label>Qui êtes-vous ?</label>
            <div class="person-grid">
              <?php foreach (array('Enseignant·e', 'Étudiant·e MMI 2 ou 3', 'Personnel USMB', 'Professionnel·le / partenaire', 'Visiteur·se extérieur·e') as $profil) { ?>
                <label class="person-card">
                  <input type="radio" name="profil" value="<?php echo e($profil); ?>">
                  <span><?php echo e($profil); ?></span>
                </label>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="notice"><strong>En cas de problème :</strong> contactez votre référent·e d'agence à l'adresse referent.mmi@univ-smb.fr. Un compte rendu vous sera envoyé par email après inscription.</div>

        <div class="form-actions">
          <a href="inscription.php?salle=<?php echo e($salleCode); ?>" class="btn secondary">Retour</a>
          <button type="submit" class="btn">Confirmer ✓</button>
        </div>
      </form>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
