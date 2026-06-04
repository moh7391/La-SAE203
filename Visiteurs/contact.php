<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'contact';
$page_title = eillusion_page_title('Contact');

// Un referent par salle.
$referents = array(
    array(
        'salle' => 'Societ-e (TP 1.1)',
        'nom' => 'Benjamin Renollet',
        'contact' => '06 51 16 83 42',
        'lien' => 'tel:0651168342'
    ),
    array(
        'salle' => 'Horizon (TP 1.2)',
        'nom' => 'Cynthia Peinnet',
        'contact' => 'cpeinnet@aol.com',
        'lien' => 'mailto:cpeinnet@aol.com'
    ),
    array(
        'salle' => "L'Envers du Decor (TP 2.1)",
        'nom' => 'Guillaume Willaime-Moulin',
        'contact' => 'Guillaume.Willaime-Moulin@etu.univ-smb.fr',
        'lien' => 'mailto:Guillaume.Willaime-Moulin@etu.univ-smb.fr'
    ),
    array(
        'salle' => 'La pepiniere (TP 2.2)',
        'nom' => 'Kilian Provot',
        'contact' => 'kilian.Provot@etu.univ-smb.fr',
        'lien' => 'mailto:kilian.Provot@etu.univ-smb.fr'
    )
);

require_once 'header.php';
?>
<main class="contact-page">
  <section class="container">
    <p class="eyebrow">Contact</p>
    <h1 class="pixel-title page">Une question ?</h1>
    <p class="lead">Pour toute demande concernant l'exposition E-LLUSION, contactez le responsable du projet ou le r&eacute;f&eacute;rent de la salle concern&eacute;e.</p>

    <div class="contact-grid">
      <article class="contact-card dark">
        <p class="eyebrow">Responsable du projet</p>
        <h2>Fran&ccedil;ois Piranda</h2>
        <p><a class="contact-link" href="mailto:francois.piranda@univ-smb.fr">francois.piranda@univ-smb.fr &rarr;</a></p>
      </article>
    </div>

    <div class="location-block">
      <h2>R&eacute;f&eacute;rents par salle</h2>
      <p>Un &eacute;tudiant r&eacute;f&eacute;rent par salle, en cas de probl&egrave;me avec votre inscription.</p>

      <div class="contact-grid">
        <?php foreach ($referents as $referent) { ?>
          <article class="contact-card">
            <p class="eyebrow"><?php echo e($referent['salle']); ?></p>
            <h2><?php echo e($referent['nom']); ?></h2>
            <p><a class="contact-link" href="<?php echo e($referent['lien']); ?>"><?php echo e($referent['contact']); ?> &rarr;</a></p>
          </article>
        <?php } ?>
      </div>
    </div>

    <div class="location-block">
      <h2>IUT de Chamb&eacute;ry - D&eacute;partement MMI</h2>
      <p>Le Bourget du Lac, 73370 - Universit&eacute; Savoie Mont Blanc</p>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
