<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$res = mysqli_query($CONNEXION,
    "SELECT inscription.id_inscription, participant.nom, participant.prenom, participant.email,
            creneau.date_creneau, creneau.heure_debut, creneau.heure_fin,
            salle.id_salle, salle.nom_salle
     FROM inscription
     JOIN participant ON participant.id_participant = inscription.id_participant
     JOIN creneau ON creneau.id_creneau = inscription.id_creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     WHERE inscription.id_inscription = $id");
$info = $res ? mysqli_fetch_assoc($res) : null;

$active_page = 'inscription';
$page_title = eillusion_page_title('Réservation confirmée');
require_once 'header.php';
?>
<main class="confirmation">
  <div class="container">
    <?php if (!$info) { ?>
      <h1 class="pixel-title page">Inscription introuvable</h1>
      <p class="lead" style="margin-left:auto;margin-right:auto;">Le récapitulatif demandé n'existe pas ou a été supprimé.</p>
      <p><a href="inscription.php" class="btn">Retour au formulaire</a></p>
    <?php } else {
        $code = eillusion_reservation_code($info['id_inscription']);
        $codeSalle = eillusion_code_from_db_salle($CONNEXION, $info['id_salle']);
    ?>
      <div class="check-icon">✓</div>
      <h1 class="pixel-title medium">C'est confirmé !</h1>
      <p>Un récapitulatif a été envoyé à <strong><?php echo e($info['email']); ?></strong>.</p>

      <div class="confirm-card">
        <span class="eyebrow">Code de réservation</span>
        <div class="confirm-code"><?php echo e($code); ?></div>

        <div class="recap-grid">
          <div>
            <span>Salle</span>
            <strong><?php echo e($codeSalle); ?></strong>
          </div>
          <div>
            <span>Personnes</span>
            <strong>1</strong>
          </div>
          <div>
            <span>Date</span>
            <strong><?php echo e(eillusion_date_label($info['date_creneau'])); ?></strong>
          </div>
          <div>
            <span>Créneau</span>
            <strong><?php echo e(eillusion_heure($info['heure_debut'])); ?></strong>
          </div>
        </div>
      </div>

      <p>Conservez votre code pour modifier ou supprimer votre réservation depuis la rubrique « Ma réservation ».</p>
      <p><a href="index.php" class="btn">Retour à l'accueil</a></p>
    <?php } ?>
  </div>
</main>
<?php require_once 'footer.php'; ?>
