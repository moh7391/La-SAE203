<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'contact';
$page_title = eillusion_page_title('Contact');
require_once 'header.php';

// Les salles viennent de la base (pour faire un referent par salle).
$salles = eillusion_get_salles($CONNEXION);
?>
<main class="contact-page">
  <section class="container">
    <p class="eyebrow">Contact</p>
    <h1 class="pixel-title page">Une question ?</h1>
    <p class="lead">Pour toute demande concernant l'exposition E-LLUSION, contactez le responsable du projet ou le référent de la salle concernée.</p>

    <div class="contact-grid">
      <article class="contact-card dark">
        <p>✉</p>
        <p class="eyebrow">Responsable du projet</p>
        <h2>François Piranda</h2>
        <p><a class="contact-link" href="mailto:francois.piranda@univ-smb.fr">francois.piranda@univ-smb.fr ↗</a></p>
      </article>
    </div>

    <div class="location-block">
      <h2>Référents par salle</h2>
      <p>Un·e étudiant·e référent·e par salle, en cas de problème avec votre inscription.</p>

      <div class="room-grid">
        <?php foreach ($salles as $salle) {
            // Email bidon construit a partir du nom de la salle (ex: referent-salle002@univ-smb.fr).
            $slug = str_replace(' ', '', strtolower($salle['nom_salle']));
            $mail = 'referent-' . $slug . '@univ-smb.fr';
        ?>
          <article class="contact-card">
            <p class="eyebrow">Référent·e</p>
            <h2><?php echo e($salle['nom_salle']); ?></h2>
            <p><a class="contact-link" href="mailto:<?php echo e($mail); ?>"><?php echo e($mail); ?> ↗</a></p>
          </article>
        <?php } ?>
      </div>
    </div>

    <div class="location-block">
      <h2>IUT de Chambéry — Département MMI</h2>
      <p>Le Bourget du Lac, 73370 — Université Savoie Mont Blanc</p>
      <div class="social-pills">
        <span>@mmi_chambery</span>
        <span>mmichambery.com</span>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
