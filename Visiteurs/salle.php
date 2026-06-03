<?php
// Page de detail d'une salle : nom global + liste des oeuvres + bouton s'inscrire.
require_once 'connexion.php';
require_once 'eillusion-data.php';

$idSalle = isset($_GET['id_salle']) ? (int) $_GET['id_salle'] : 0;
$salle = eillusion_get_salle($CONNEXION, $idSalle);

$active_page = 'salles';
$page_title = $salle ? eillusion_page_title($salle['nom_salle']) : eillusion_page_title('Salle');
require_once 'header.php';
?>
<main>
  <section class="page-section">
    <div class="container">
      <a href="salles.php" class="back-link">← Toutes les salles</a>

      <?php if (!$salle) { ?>
        <h1 class="pixel-title page">Salle introuvable</h1>
      <?php } else {
          // Les oeuvres de cette salle (tables contenir + element_expo).
          $elements = eillusion_get_elements($CONNEXION, $salle['id_salle']);
      ?>
        <p class="eyebrow"><?php echo e($salle['nom_salle']); ?></p>
        <h1 class="pixel-title page"><?php echo e($salle['nom_salle']); ?></h1>
        <p class="lead"><?php echo e($salle['description']); ?></p>

        <div class="detail-card">
          <h2>Œuvres exposées</h2>
          <?php if (count($elements) == 0) { ?>
            <p>Aucune œuvre renseignée pour cette salle.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($elements as $el) { ?>
                <li><?php echo e($el['titre']); ?></li>
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
