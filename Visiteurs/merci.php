<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// On recupere les details de l'inscription depuis la base.
$res = mysqli_query($CONNEXION,
    "SELECT inscription.id_inscription, participant.nom, participant.prenom, participant.email,
            creneau.date_creneau, creneau.heure_debut, creneau.heure_fin, salle.nom_salle
     FROM inscription
     JOIN participant ON participant.id_participant = inscription.id_participant
     JOIN creneau ON creneau.id_creneau = inscription.id_creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     WHERE inscription.id_inscription = $id");
$info = mysqli_fetch_assoc($res);

$active_page = 'inscription';
$page_title = eillusion_page_title('Réservation confirmée');
require_once 'header.php';
?>
<main class="confirmation">
  <div class="container">
    <?php if (!$info) { ?>
      <h1 class="pixel-title medium">Inscription introuvable</h1>
      <p><a href="inscription.php" class="btn">Retour au formulaire</a></p>
    <?php } else { ?>
      <div class="check-icon">✓</div>
      <h1 class="pixel-title medium">C'est confirmé !</h1>
      <p>Un récapitulatif a été envoyé à <strong><?php echo e($info['email']); ?></strong>.</p>

      <div class="confirm-card">
        <span class="eyebrow">Code de réservation</span>
        <div class="confirm-code"><?php echo e(eillusion_reservation_code($info['id_inscription'])); ?></div>

        <div class="recap-grid">
          <div>
            <span>Salle</span>
            <strong><?php echo e($info['nom_salle']); ?></strong>
          </div>
          <div>
            <span>Date</span>
            <strong><?php echo e(eillusion_date_courte($info['date_creneau'])); ?></strong>
          </div>
          <div>
            <span>Créneau</span>
            <strong><?php echo e(eillusion_heure($info['heure_debut']) . ' à ' . eillusion_heure($info['heure_fin'])); ?></strong>
          </div>
          <div>
            <span>Nom</span>
            <strong><?php echo e($info['prenom'] . ' ' . $info['nom']); ?></strong>
          </div>
        </div>
      </div>

      <p>Conservez votre code pour modifier ou annuler votre réservation depuis « Ma réservation ».</p>
      <p><a href="index.php" class="btn">Retour à l'accueil</a></p>
    <?php } ?>
  </div>
</main>
<?php require_once 'footer.php'; ?>
