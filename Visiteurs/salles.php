<?php
// Page publique : liste des salles de l'exposition (depuis la base).
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'salles';
$page_title = eillusion_page_title('Les salles');
require_once 'header.php';

$salles = eillusion_get_salles($CONNEXION);
?>
<main class="salles-page">
  <section class="page-section">
    <div class="container">
      <div class="program-head">
        <p class="eyebrow">Programme</p>
        <h1 class="pixel-title page">Les salles</h1>
        <p class="lead">Chaque salle est conçue par une agence d'étudiant·es MMI1. Découvrez les univers et choisissez votre créneau.</p>
      </div>

      <div class="room-grid two">
        <?php foreach ($salles as $salle) {
            // Les oeuvres de cette salle (depuis la base).
            $elements = eillusion_get_elements($CONNEXION, $salle['id_salle']);
        ?>
          <article class="room-card large">
            <span class="badge light max-badge">12 max</span>
            <div class="room-number"><?php echo e($salle['nom_salle']); ?></div>
            <p><?php echo e($salle['description']); ?></p>
            <div class="tag-row">
              <?php foreach ($elements as $el) { ?>
                <span class="tag"><?php echo e($el['titre']); ?></span>
              <?php } ?>
            </div>
            <div class="card-actions">
              <a href="salle.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="text-link">En savoir plus →</a>
              <a href="inscription.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="btn">Réserver</a>
            </div>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
