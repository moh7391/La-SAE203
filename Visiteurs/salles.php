<?php
// Page visiteur : liste des salles.

require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'salles';
$page_title = eillusion_page_title('Les salles');

$salles = eillusion_get_salles($CONNEXION);

require_once 'header.php';
?>
<main class="salles-page">
  <section class="page-section">
    <div class="container">
      <div class="program-head">
        <p class="eyebrow">Programme</p>
        <h1 class="pixel-title page">Les salles</h1>
        <p class="lead">Chaque salle est con&ccedil;ue par une agence d'&eacute;tudiants MMI1. D&eacute;couvrez les univers et choisissez votre cr&eacute;neau.</p>
      </div>

      <div class="room-grid two">
        <?php foreach ($salles as $salle) { ?>
          <?php $elements = eillusion_get_elements($CONNEXION, $salle['id_salle']); ?>

          <article class="room-card large">
            <span class="badge light max-badge">12 max</span>
            <div class="room-number"><?php echo e($salle['nom_salle']); ?></div>
            <p><?php echo e($salle['description']); ?></p>

            <div class="tag-row">
              <?php foreach ($elements as $element) { ?>
                <span class="tag"><?php echo e($element['titre']); ?></span>
              <?php } ?>
            </div>

            <div class="card-actions">
              <a href="salle.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="text-link">En savoir plus &rarr;</a>
              <a href="inscription.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="btn">R&eacute;server</a>
            </div>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
