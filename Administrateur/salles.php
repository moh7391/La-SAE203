<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'salles';
$page_title = eillusion_page_title('Les salles');
require_once 'header.php';
$salles = eillusion_salles();
?>
<main class="salles-page">
  <section class="page-section">
    <div class="container">
      <div class="program-head">
        <p class="eyebrow">Programme</p>
        <h1 class="pixel-title page">Les 4 salles</h1>
        <p class="lead">Chaque salle est conçue par une agence d'étudiant·es MMI1. Découvrez les univers, choisissez votre créneau et plongez dans l'illusion.</p>
      </div>

      <div class="room-grid two">
        <?php foreach ($salles as $salle) { ?>
          <article class="room-card large">
            <span class="badge light max-badge">12 max</span>
            <div class="room-number"><?php echo e($salle['code']); ?></div>
            <h2><?php echo e($salle['titre']); ?></h2>
            <p><?php echo e($salle['description']); ?></p>
            <div class="tag-row">
              <?php foreach ($salle['tags'] as $tag) { ?>
                <span class="tag"><?php echo e($tag); ?></span>
              <?php } ?>
            </div>
            <div class="card-actions">
              <a href="salle.php?code=<?php echo e($salle['code']); ?>" class="text-link">En savoir plus →</a>
              <a href="inscription.php?salle=<?php echo e($salle['code']); ?>" class="btn">Réserver</a>
            </div>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
