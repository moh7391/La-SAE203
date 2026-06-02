<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'contact';
$page_title = eillusion_page_title('Contact');
require_once 'header.php';
?>
<main class="contact-page">
  <section class="container">
    <p class="eyebrow">Contact</p>
    <h1 class="pixel-title page">Une question ?</h1>
    <p class="lead">Pour toute demande concernant l'exposition E-LLUSION, l'organisation ou un cas particulier, contactez la responsable du projet.</p>

    <div class="contact-grid">
      <article class="contact-card dark">
        <p>✉</p>
        <p class="eyebrow">Responsable du projet</p>
        <h2>Noémie Maulant</h2>
        <p><a class="contact-link" href="mailto:noemie.maulant@univ-smb.fr">noemie.maulant@univ-smb.fr ↗</a></p>
      </article>

      <article class="contact-card">
        <p>✉</p>
        <p class="eyebrow">Référent·e inscriptions</p>
        <h2>Étudiant·e d'agence</h2>
        <p><a class="contact-link" href="mailto:referent.mmi@univ-smb.fr">referent.mmi@univ-smb.fr ↗</a></p>
        <p>En cas de problème avec votre inscription ou cas particulier.</p>
      </article>
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
