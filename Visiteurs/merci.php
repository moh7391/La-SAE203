<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$idInscription = 0;

if (isset($_GET['id'])) {
    $idInscription = (int) $_GET['id'];
}

$sql = "SELECT inscription.id_inscription, participant.nom, participant.prenom,
               participant.email, creneau.date_creneau, creneau.heure_debut,
               creneau.heure_fin, salle.nom_salle
        FROM inscription
        JOIN participant ON participant.id_participant = inscription.id_participant
        JOIN creneau ON creneau.id_creneau = inscription.id_creneau
        JOIN salle ON salle.id_salle = creneau.id_salle
        WHERE inscription.id_inscription = $idInscription";

$resultat = mysqli_query($CONNEXION, $sql);
$info = mysqli_fetch_assoc($resultat);

$active_page = 'inscription';
$page_title = eillusion_page_title('Reservation confirmee');

require_once 'header.php';
?>
<main class="confirmation">
  <div class="container">
    <?php if (!$info) { ?>
      <h1 class="pixel-title medium">Inscription introuvable</h1>
      <p><a href="inscription.php" class="btn">Retour au formulaire</a></p>
    <?php } else { ?>
      <div class="check-icon">&#10003;</div>
      <h1 class="pixel-title medium">C'est confirm&eacute; !</h1>
      <p>Un r&eacute;capitulatif a &eacute;t&eacute; envoy&eacute; &agrave; <strong><?php echo e($info['email']); ?></strong>.</p>

      <div class="confirm-card">
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
            <span>Cr&eacute;neau</span>
            <strong>
              <?php echo e(eillusion_heure($info['heure_debut'])); ?>
              &agrave;
              <?php echo e(eillusion_heure($info['heure_fin'])); ?>
            </strong>
          </div>

          <div>
            <span>Nom</span>
            <strong><?php echo e($info['prenom'] . ' ' . $info['nom']); ?></strong>
          </div>
        </div>
      </div>

      <p>Pour modifier ou annuler votre r&eacute;servation, rendez-vous dans &laquo; Ma r&eacute;servation &raquo; avec votre adresse e-mail.</p>
      <p><a href="index.php" class="btn">Retour &agrave; l'accueil</a></p>
    <?php } ?>
  </div>
</main>
<?php require_once 'footer.php'; ?>
