<?php
// Gestion des creneaux.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

$filtreSalle = isset($_GET['salle']) ? (int) $_GET['salle'] : 0;
$filtreDate = isset($_GET['date']) ? $_GET['date'] : '';

$salles = eillusion_db_salles($CONNEXION);
$creneaux = eillusion_get_creneaux($CONNEXION, 0);

if ($filtreSalle > 0 || $filtreDate !== '') {
    $creneaux = array_filter($creneaux, function ($c) use ($filtreSalle, $filtreDate) {
        if ($filtreSalle > 0 && (int) $c['id_salle'] !== $filtreSalle) {
            return false;
        }
        if ($filtreDate !== '' && $c['date_creneau'] !== $filtreDate) {
            return false;
        }
        return true;
    });
}

$page_title = 'Gestion des creneaux - E-LLUSION admin';
$active_page = 'creneaux';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Gestion des créneaux</h1>
    <a class="admin-btn small" href="creneaux.php">+ Ajouter un créneau</a>
  </section>

  <form class="admin-card filter-card" method="get" action="creneaux.php">
    <div class="filter-grid">
      <div class="field">
        <label for="salle">Filtrer par salle</label>
        <select id="salle" name="salle" onchange="this.form.submit()">
          <option value="">Toutes les salles</option>
          <?php foreach ($salles as $salle) { ?>
            <option value="<?php echo (int) $salle['id_salle']; ?>"<?php if ($filtreSalle === (int) $salle['id_salle']) echo ' selected'; ?>>
              <?php echo admin_e($salle['nom_salle']); ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="field">
        <label for="date">Filtrer par date</label>
        <input type="date" id="date" name="date" value="<?php echo admin_e($filtreDate); ?>" onchange="this.form.submit()">
      </div>
    </div>
  </form>

  <section class="admin-card panel">
    <div class="table-scroll">
      <table class="admin-table compact-table">
        <thead>
          <tr>
            <th>Salle</th>
            <th>Date</th>
            <th>Heure début</th>
            <th>Heure fin</th>
            <th>Jauge max</th>
            <th>Inscrits</th>
            <th>Places restantes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($creneaux) === 0) { ?>
            <tr>
              <td colspan="8">Aucun créneau trouvé.</td>
            </tr>
          <?php } ?>
          <?php $index = 0; foreach ($creneaux as $c) { $index++; ?>
            <tr<?php if ($index % 2 === 0) echo ' class="row-soft"'; ?>>
              <td><?php echo admin_e($c['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_date_courte($c['date_creneau'])); ?></td>
              <td><?php echo admin_e(eillusion_heure($c['heure_debut'])); ?></td>
              <td><?php echo admin_e(eillusion_heure($c['heure_fin'])); ?></td>
              <td><?php echo (int) $c['jauge']; ?></td>
              <td><?php echo (int) $c['nb_inscrits']; ?></td>
              <td>
                <?php if ((int) $c['places_restantes'] <= 0) { ?>
                  <span class="status-full">0 (Complet)</span>
                <?php } else { ?>
                  <span class="stat-inline"><?php echo (int) $c['places_restantes']; ?></span>
                <?php } ?>
              </td>
              <td>
                <div class="btn-row">
                  <a class="admin-pill" href="creneaux.php">Modifier</a>
                  <a class="admin-danger" href="creneaux.php">Supprimer</a>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<?php include 'footer.php'; ?>
