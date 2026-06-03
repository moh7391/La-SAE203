<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'contact';
$page_title = eillusion_page_title('Contact');
require_once 'header.php';

// Referents : un par salle / TP.
$referents = array(
    array('salle' => 'Societ-e (TP 1.1)',          'nom' => 'Benjamin Renollet',         'contact' => '06 51 16 83 42',                            'lien' => 'tel:0651168342'),
    array('salle' => 'Horizon (TP 1.2)',           'nom' => 'Cynthia Peinnet',           'contact' => 'cpeinnet@aol.com',                          'lien' => 'mailto:cpeinnet@aol.com'),
    array('salle' => "L'Envers du Décor (TP 2.1)", 'nom' => 'Guillaume Willaime-Moulin', 'contact' => 'Guillaume.Willaime-Moulin@etu.univ-smb.fr', 'lien' => 'mailto:Guillaume.Willaime-Moulin@etu.univ-smb.fr'),
    array('salle' => 'La pépinière (TP 2.2)',      'nom' => 'Kilian Provot',             'contact' => 'kilian.Provot@etu.univ-smb.fr',             'lien' => 'mailto:kilian.Provot@etu.univ-smb.fr'),
);
?>
<main class="contact-page">
  <section class="container">
    <p class="eyebrow">Contact</p>
    <h1 class="pixel-title page">Une question ?</h1>
    <p class="lead">Pour toute demande concernant l'exposition E-LLUSION, contactez le responsable du projet ou le référent de la salle concernée.</p>

    <div class="contact-grid">
      <article class="contact-card dark">
        <p class="eyebrow">Responsable du projet</p>
        <h2>François Piranda</h2>
        <p><a class="contact-link" href="mailto:francois.piranda@univ-smb.fr">francois.piranda@univ-smb.fr ↗</a></p>
      </article>
    </div>

    <div class="location-block">
      <h2>Référents par salle</h2>
      <p>Un·e étudiant·e référent·e par salle, en cas de problème avec votre inscription.</p>

      <div class="contact-grid">
        <?php foreach ($referents as $r) { ?>
          <article class="contact-card">
            <p class="eyebrow"><?php echo e($r['salle']); ?></p>
            <h2><?php echo e($r['nom']); ?></h2>
            <p><a class="contact-link" href="<?php echo e($r['lien']); ?>"><?php echo e($r['contact']); ?> ↗</a></p>
          </article>
        <?php } ?>
      </div>
    </div>

    <div class="location-block">
      <h2>IUT de Chambéry — Département MMI</h2>
      <p>Le Bourget du Lac, 73370 — Université Savoie Mont Blanc</p>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
