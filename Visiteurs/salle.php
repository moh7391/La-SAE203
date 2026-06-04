<?php
// Page visiteur : detail d'une salle.

require_once 'connexion.php';
require_once 'eillusion-data.php';

$idSalle = 0;

if (isset($_GET['id_salle'])) {
    $idSalle = (int) $_GET['id_salle'];
}

$salle = eillusion_get_salle($CONNEXION, $idSalle);

$active_page = 'salles';
$page_title = eillusion_page_title('Salle');

if ($salle) {
    $page_title = eillusion_page_title($salle['nom_salle']);
}

require_once 'header.php';
?>
<main>
  <section class="page-section">
    <div class="container">
      <a href="salles.php" class="back-link">&larr; Toutes les salles</a>

      <?php if (!$salle) { ?>
        <h1 class="pixel-title page">Salle introuvable</h1>
      <?php } else { ?>
        <?php $elements = eillusion_get_elements($CONNEXION, $salle['id_salle']); ?>

        <p class="eyebrow"><?php echo e($salle['nom_salle']); ?></p>
        <h1 class="pixel-title page"><?php echo e($salle['nom_salle']); ?></h1>
        <p class="lead"><?php echo e($salle['description']); ?></p>

        <div class="detail-card">
          <h2>Oeuvres expos&eacute;es</h2>

          <?php if (count($elements) == 0) { ?>
            <p>Aucune oeuvre renseign&eacute;e pour cette salle.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($elements as $element) { ?>
                <li><?php echo e($element['titre']); ?></li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>

        <p>
          <a class="btn" href="inscription.php?id_salle=<?php echo (int) $salle['id_salle']; ?>">S'inscrire</a>
        </p>
      <?php } ?>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
