<?php
// Page de detail d'une salle : description, oeuvres et creneaux (tout depuis la base).
require_once 'connexion.php';
require_once 'eillusion-data.php';

$idSalle = isset($_GET['id_salle']) ? (int) $_GET['id_salle'] : 0;
$salle = eillusion_get_salle($CONNEXION, $idSalle);

$active_page = 'salles';
require_once 'header.php';
?>
<main>
  <section class="page-section">
    <div class="container">
      <a href="salles.php" class="back-link">← Toutes les salles</a>

      <?php if (!$salle) { ?>
        <h1 class="pixel-title page">Salle introuvable</h1>
      <?php } else {
          $elements = eillusion_get_elements($CONNEXION, $salle['id_salle']);
          $creneaux = eillusion_get_creneaux($CONNEXION, $salle['id_salle']);
      ?>
        <p class="eyebrow"><?php echo e($salle['nom_salle']); ?></p>
        <h1 class="pixel-title page"><?php echo e($salle['nom_salle']); ?></h1>
        <p class="lead"><?php echo e($salle['description']); ?></p>

        <div class="detail-card">
          <h2>Œuvres exposées</h2>
          <?php if (count($elements) === 0) { ?>
            <p>Aucune œuvre renseignée pour cette salle.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($elements as $el) { ?>
                <li><strong><?php echo e($el['titre']); ?></strong> — <?php echo e($el['description']); ?></li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>

        <div class="detail-card">
          <h2>Créneaux disponibles</h2>
          <?php if (count($creneaux) === 0) { ?>
            <p>Aucun créneau pour cette salle.</p>
          <?php } else { ?>
            <div class="table-wrap">
              <table>
                <caption>Créneaux de <?php echo e($salle['nom_salle']); ?></caption>
                <tr>
                  <th>Date</th>
                  <th>Horaire</th>
                  <th>Places restantes</th>
                  <th>Action</th>
                </tr>
                <?php foreach ($creneaux as $c) { ?>
                  <tr>
                    <td><?php echo e(eillusion_date_courte($c['date_creneau'])); ?></td>
                    <td><?php echo e(eillusion_heure($c['heure_debut']) . ' à ' . eillusion_heure($c['heure_fin'])); ?></td>
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
              </table>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </section>
</main>
<?php require_once 'footer.php'; ?>
