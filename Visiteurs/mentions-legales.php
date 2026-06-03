<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'mentions';
$page_title = eillusion_page_title('Mentions légales');
require_once 'header.php';
?>
<main class="legal-page">
  <section class="container">
    <p class="eyebrow">Informations</p>
    <h1 class="pixel-title page">Mentions légales</h1>
    <p class="lead">Cette page rassemble les informations utiles concernant le site de l'exposition E-LLUSION.</p>

    <div class="legal-grid">
      <article class="legal-card dark">
        <h2>Éditeur du site</h2>
        <p>E-LLUSION, exposition pédagogique des étudiant·es MMI1.</p>
        <p>IUT de Chambéry - Département MMI<br>Le Bourget du Lac, 73370</p>
      </article>

      <article class="legal-card">
        <h2>Responsable du projet</h2>
        <p>François Piranda</p>
        <p><a class="contact-link" href="mailto:francois.piranda@univ-smb.fr">francois.piranda@univ-smb.fr ↗</a></p>
      </article>

      <article class="legal-card">
        <h2>Données personnelles</h2>
        <p>Les informations saisies lors d'une réservation servent uniquement à gérer les inscriptions à l'exposition.</p>
        <p>Pour toute demande de modification ou de suppression, utilisez la page <a class="contact-link" href="contact.php">contact</a>.</p>
      </article>

      <article class="legal-card">
        <h2>Propriété intellectuelle</h2>
        <p>Les contenus, textes, visuels et œuvres présentés sur ce site appartiennent à leurs auteur·ices respectif·ves.</p>
        <p>Toute réutilisation doit faire l'objet d'une autorisation préalable.</p>
      </article>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
