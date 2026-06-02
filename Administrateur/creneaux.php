<?php
// Page de secours : liste SQL des créneaux avec places restantes.
// Elle garde l'ancien fonctionnement sous une forme compatible avec la nouvelle charte.
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'inscription';
$page_title = eillusion_page_title('Créneaux');
$creneaux = eillusion_get_creneaux($CONNEXION, 0);
require_once 'header.php';
?>
<main>
  <section class="page-section">
    <div class="container">
      <p class="eyebrow">Créneaux</p>
      <h1 class="pixel-title page">Réserver<br>un créneau</h1>
      <p class="lead">Choisissez un créneau puis cliquez sur « S'inscrire ». Cette page utilise directement la base SQL.</p>

      <div class="table-wrap">
        <table>
          <caption>Liste des créneaux</caption>
          <thead>
            <tr>
              <th>Date</th>
              <th>Horaire</th>
              <th>Salle</th>
              <th>Places restantes</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($creneaux as $c) { ?>
              <tr>
                <td><?php echo e(eillusion_date_courte($c['date_creneau'])); ?></td>
                <td><?php echo e(eillusion_heure($c['heure_debut']) . ' à ' . eillusion_heure($c['heure_fin'])); ?></td>
                <td><?php echo e($c['nom_salle']); ?></td>
                <td><?php echo (int) $c['places_restantes']; ?> / <?php echo (int) $c['jauge']; ?></td>
                <td>
                  <?php if ((int) $c['places_restantes'] > 0) { ?>
                    <a class="btn" href="inscription.php?id_creneau=<?php echo (int) $c['id_creneau']; ?>">S'inscrire</a>
                  <?php } else { ?>
                    <span class="badge light">Complet</span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
