<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'accueil';
$page_title = eillusion_page_title('Accueil');
require_once 'header.php';
$salles = eillusion_salles();
?>
<main>
  <section class="hero">
    <div class="container">
      <div class="capsule">Exposition MMI1 · 18–19 juin 2026</div>
      <h1 class="pixel-title big">E-LLUSION</h1>
      <h2>L’exposition immersive des étudiant·es MMI1</h2>
      <p>Quatre salles, quatre univers, une illusion totale. Préparez-vous à douter de ce que vous voyez.</p>
      <div class="hero-actions">
        <a href="inscription.php" class="btn">Réserver mon créneau</a>
        <a href="salles.php" class="btn outline">Découvrir les salles</a>
      </div>
    </div>
  </section>

  <section class="concept" id="concept">
    <div class="container concept-grid">
      <div>
        <p class="eyebrow">Le concept</p>
        <h2 class="pixel-title medium">Quand le réel....</h2>
        <p>Voici une zone de texte qui présente rapidement l'exposition. Chaque salle est une agence MMI, un univers singulier conçu de A à Z par des étudiant·es passionné·es de design, de code et de narration.</p>
        <p>À travers la lumière, les reflets, le glitch et la réalité augmentée, E-LLUSION propose un parcours immersif où la perception devient un terrain de jeu.</p>
      </div>
      <div class="concept-visual" aria-hidden="true">
        <div class="eye"></div>
      </div>
    </div>
  </section>

  <section class="rooms-home">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">4 salles, 4 univers</p>
          <h2 class="pixel-title medium">Les salles</h2>
        </div>
        <a href="salles.php" class="section-link">Voir toutes les salles →</a>
      </div>

      <div class="room-grid">
        <?php foreach ($salles as $salle) { ?>
          <article class="room-card">
            <div class="room-number"><?php echo e($salle['code']); ?></div>
            <h3><?php echo e($salle['titre']); ?></h3>
            <p><?php echo e($salle['resume']); ?></p>
            <a href="salle.php?code=<?php echo e($salle['code']); ?>" class="text-link teal">Découvrir →</a>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
