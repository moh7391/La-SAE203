<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'mentions';
$page_title = eillusion_page_title('Mentions legales');

require_once 'header.php';
?>
<main class="legal-page">
  <section class="container">
    <p class="eyebrow">Informations</p>
    <h1 class="pixel-title page">Mentions l&eacute;gales</h1>
    <p class="lead">Cette page rassemble les informations utiles concernant le site de l'exposition E-LLUSION.</p>

    <div class="legal-grid">
      <article class="legal-card dark">
        <h2>&Eacute;diteur du site</h2>
        <p>E-LLUSION, exposition p&eacute;dagogique des &eacute;tudiants MMI1.</p>
        <p>IUT de Chamb&eacute;ry - D&eacute;partement MMI<br>Le Bourget du Lac, 73370</p>
      </article>

      <article class="legal-card">
        <h2>Responsable du projet</h2>
        <p>Fran&ccedil;ois Piranda</p>
        <p><a class="contact-link" href="mailto:francois.piranda@univ-smb.fr">francois.piranda@univ-smb.fr &rarr;</a></p>
      </article>

      <article class="legal-card">
        <h2>Donn&eacute;es personnelles</h2>
        <p>Les informations saisies lors d'une r&eacute;servation servent uniquement &agrave; g&eacute;rer les inscriptions &agrave; l'exposition.</p>
        <p>Pour toute demande de modification ou de suppression, utilisez la page <a class="contact-link" href="contact.php">contact</a>.</p>
      </article>

      <article class="legal-card">
        <h2>Propri&eacute;t&eacute; intellectuelle</h2>
        <p>Les contenus, textes, visuels et oeuvres pr&eacute;sent&eacute;s sur ce site appartiennent &agrave; leurs auteurs respectifs.</p>
        <p>Toute r&eacute;utilisation doit faire l'objet d'une autorisation pr&eacute;alable.</p>
      </article>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
