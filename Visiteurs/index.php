<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'accueil';
$page_title = eillusion_page_title('Accueil');
require_once 'header.php';

// Les salles viennent de la base.
$salles = eillusion_get_salles($CONNEXION);
?>
<aside class="accessibility-panel" aria-label="Options d'affichage">
  <div class="accessibility-title">Affichage</div>
  <div class="accessibility-group" aria-label="Taille du texte">
    <button type="button" data-font-size="normal" aria-label="Taille normale">A</button>
    <button type="button" data-font-size="large" aria-label="Texte agrandi">A+</button>
  </div>
  <div class="accessibility-group" aria-label="Contraste">
    <button type="button" data-contrast="normal" aria-label="Contraste normal">Normal</button>
    <button type="button" data-contrast="high" aria-label="Contraste élevé">Contraste +</button>
  </div>
</aside>
<main>
  <section class="hero">
    <div class="container">
      <div class="capsule">Exposition MMI1 · 18–19 juin 2026</div>
      <h1 class="pixel-title big">E-LLUSION</h1>
      <h2>L'exposition immersive des étudiant·es MMI1</h2>
      <p>Quatre salles, quatre univers, une illusion totale. Préparez-vous à douter de ce que vous voyez.</p>
      <div class="hero-actions">
        <a href="inscription.php" class="btn">Réserver mon créneau</a>
        <a href="salles.php" class="btn outline">Découvrir les salles</a>
      </div>
    </div>
  </section>

  <section class="concept-section">
    <div class="container concept-grid">
      <div class="concept-text">
        <p class="concept-eyebrow">Le concept</p>
        <h2 class="pixel-title concept-title">Quand le r&eacute;el.....</h2>
        <p>Voici une zone de texte qui pr&eacute;sente rapidement l'exposition totale E-LLUSION.</p>
        <p>Chaque salle est une exp&eacute;rience MMI, un univers singulier con&ccedil;u pour d&eacute;clencher des sensations et des illusions.</p>
      </div>
      <a class="concept-video-link" href="https://www.youtube.com/" target="_blank" rel="noopener">
        <span class="play-icon" aria-hidden="true"></span>
        <span>Voir la vid&eacute;o</span>
      </a>
    </div>
  </section>

  <section class="rooms-home">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">Les salles de l'exposition</p>
          <h2 class="pixel-title medium">Les salles</h2>
        </div>
        <a href="salles.php" class="section-link">Voir toutes les salles →</a>
      </div>

      <div class="room-grid">
        <?php foreach ($salles as $salle) { ?>
          <article class="room-card">
            <h3 class="room-number"><?php echo e($salle['nom_salle']); ?></h3>
            <p><?php echo e($salle['description']); ?></p>
            <div class="card-actions">
              <a href="salle.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="text-link">En savoir plus →</a>
              <a href="inscription.php?id_salle=<?php echo (int) $salle['id_salle']; ?>" class="text-link teal">Choisir un créneau →</a>
            </div>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
