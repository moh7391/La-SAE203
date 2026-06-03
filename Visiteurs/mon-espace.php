<?php
// Ma reservation : le visiteur entre SON email (celui de l'inscription).
// On retrouve ses reservations dans la base, il peut changer de creneau ou annuler.
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'reservation';
$page_title = eillusion_page_title('Ma réservation');
$message = '';
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'rechercher';
    $email = mysqli_real_escape_string($CONNEXION, trim($_POST['email']));

    // Annuler une inscription (seulement si l'email correspond bien au participant).
    if ($action === 'annuler') {
        $id = (int) $_POST['id_inscription'];
        mysqli_query($CONNEXION,
            "DELETE inscription FROM inscription
             JOIN participant ON participant.id_participant = inscription.id_participant
             WHERE inscription.id_inscription = $id AND participant.email = '$email'");
        $message = 'Inscription annulée.';
    }

    // Changer le creneau d'une inscription.
    if ($action === 'modifier') {
        $id = (int) $_POST['id_inscription'];
        $nouveauCreneau = (int) $_POST['nouveau_creneau'];
        $creneau = eillusion_get_creneau($CONNEXION, $nouveauCreneau);

        if (!$creneau || $creneau['places_restantes'] <= 0) {
            $erreur = 'Ce créneau est complet.';
        } else {
            mysqli_query($CONNEXION,
                "UPDATE inscription
                 JOIN participant ON participant.id_participant = inscription.id_participant
                 SET inscription.id_creneau = $nouveauCreneau
                 WHERE inscription.id_inscription = $id AND participant.email = '$email'");
            $message = 'Créneau modifié.';
        }
    }

    if ($email === '') {
        $erreur = 'Merci d\'entrer votre email.';
    }
}

// Si un email a ete saisi, on cherche ses reservations dans la base.
$reservations = array();
if ($email !== '') {
    $res = mysqli_query($CONNEXION,
        "SELECT inscription.id_inscription, creneau.id_creneau, creneau.date_creneau,
                creneau.heure_debut, creneau.heure_fin, salle.nom_salle,
                participant.prenom, participant.nom
         FROM inscription
         JOIN participant ON participant.id_participant = inscription.id_participant
         JOIN creneau ON creneau.id_creneau = inscription.id_creneau
         JOIN salle ON salle.id_salle = creneau.id_salle
         WHERE participant.email = '$email'
         ORDER BY creneau.date_creneau, creneau.heure_debut");
    while ($r = mysqli_fetch_assoc($res)) {
        $reservations[] = $r;
    }
    if (count($reservations) === 0 && $erreur === '' && $message === '') {
        $erreur = 'Aucune réservation trouvée pour cet email.';
    }
}

require_once 'header.php';
?>
<main class="reservation-page">
  <section class="container">
    <p class="eyebrow">Gérer ma réservation</p>
    <h1 class="pixel-title page">Modifier ou annuler</h1>
    <p class="lead">Entrez l'adresse e-mail utilisée lors de votre inscription pour retrouver vos réservations.</p>

    <?php if ($message !== '') { ?><div class="success"><?php echo e($message); ?></div><?php } ?>
    <?php if ($erreur !== '') { ?><div class="error"><?php echo e($erreur); ?></div><?php } ?>

    <form action="mon-espace.php" method="post" class="panel">
      <input type="hidden" name="action" value="rechercher">
      <div class="field">
        <label for="email">Adresse e-mail</label>
        <input class="input" type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
      </div>
      <div style="margin-top:16px;">
        <button type="submit" class="btn">Rechercher</button>
      </div>
    </form>

    <?php foreach ($reservations as $r) {
        // Liste des creneaux disponibles pour le menu "changer de creneau".
        $listeCreneaux = eillusion_get_creneaux($CONNEXION, 0);
    ?>
      <article class="reservation-item">
        <h2><?php echo e($r['prenom'] . ' ' . $r['nom']); ?></h2>
        <p><strong>Salle :</strong> <?php echo e($r['nom_salle']); ?></p>
        <p><strong>Date :</strong> <?php echo e(eillusion_date_label($r['date_creneau'])); ?>
           à <?php echo e(eillusion_heure($r['heure_debut'])); ?></p>

        <div class="reservation-actions">
          <!-- Changer de creneau -->
          <form action="mon-espace.php" method="post">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id_inscription" value="<?php echo (int) $r['id_inscription']; ?>">
            <input type="hidden" name="email" value="<?php echo e($email); ?>">
            <label for="c<?php echo (int) $r['id_inscription']; ?>">Changer de créneau :</label>
            <select class="input" id="c<?php echo (int) $r['id_inscription']; ?>" name="nouveau_creneau" required>
              <option value="">-- Choisir --</option>
              <?php foreach ($listeCreneaux as $c) {
                  $complet = $c['places_restantes'] <= 0;
                  $texte = eillusion_date_courte($c['date_creneau']) . ' ' . eillusion_heure($c['heure_debut']) . ' - ' . $c['nom_salle'];
              ?>
                <option value="<?php echo (int) $c['id_creneau']; ?>" <?php echo $complet ? 'disabled' : ''; ?>>
                  <?php echo e($texte . ($complet ? ' (complet)' : '')); ?>
                </option>
              <?php } ?>
            </select>
            <button type="submit" class="btn" style="margin-top:10px;">Modifier</button>
          </form>

          <!-- Annuler -->
          <form action="mon-espace.php" method="post">
            <input type="hidden" name="action" value="annuler">
            <input type="hidden" name="id_inscription" value="<?php echo (int) $r['id_inscription']; ?>">
            <input type="hidden" name="email" value="<?php echo e($email); ?>">
            <button type="submit" class="btn secondary">Annuler cette réservation</button>
          </form>
        </div>
      </article>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
