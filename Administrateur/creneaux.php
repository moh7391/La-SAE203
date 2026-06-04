<?php
// Page administrateur : liste des creneaux.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

$filtreSalle = 0;
$filtreDate = '';

if (isset($_GET['salle'])) {
    $filtreSalle = (int) $_GET['salle'];
}

if (isset($_GET['date'])) {
    $filtreDate = $_GET['date'];
}

$salles = eillusion_db_salles($CONNEXION);
$tousLesCreneaux = eillusion_get_creneaux($CONNEXION, 0);
$creneaux = array();

// On garde seulement les creneaux qui correspondent aux filtres.
foreach ($tousLesCreneaux as $creneau) {
    $garder = true;

    if ($filtreSalle > 0 && (int) $creneau['id_salle'] != $filtreSalle) {
        $garder = false;
    }

    if ($filtreDate != '' && $creneau['date_creneau'] != $filtreDate) {
        $garder = false;
    }

    if ($garder) {
        $creneaux[] = $creneau;
    }
}

$page_title = 'Gestion des creneaux - E-LLUSION admin';
$active_page = 'creneaux';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Gestion des cr&eacute;neaux</h1>
    <a class="admin-btn small" href="creneaux.php">+ Ajouter un cr&eacute;neau</a>
  </section>

  <form class="admin-card filter-card" method="get" action="creneaux.php">
    <div class="filter-grid">
      <div class="field">
        <label for="salle">Filtrer par salle</label>

        <select id="salle" name="salle" onchange="this.form.submit()">
          <option value="">Toutes les salles</option>

          <?php foreach ($salles as $salle) { ?>
            <option value="<?php echo (int) $salle['id_salle']; ?>" <?php if ($filtreSalle == (int) $salle['id_salle']) { echo 'selected'; } ?>>
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
            <th>Heure d&eacute;but</th>
            <th>Heure fin</th>
            <th>Jauge max</th>
            <th>Inscrits</th>
            <th>Places restantes</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php if (count($creneaux) == 0) { ?>
            <tr>
              <td colspan="8">Aucun cr&eacute;neau trouv&eacute;.</td>
            </tr>
          <?php } ?>

          <?php $numeroLigne = 0; ?>

          <?php foreach ($creneaux as $creneau) { ?>
            <?php
            $numeroLigne = $numeroLigne + 1;
            $classeLigne = '';

            if ($numeroLigne % 2 == 0) {
                $classeLigne = ' class="row-soft"';
            }
            ?>

            <tr<?php echo $classeLigne; ?>>
              <td><?php echo admin_e($creneau['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_date_courte($creneau['date_creneau'])); ?></td>
              <td><?php echo admin_e(eillusion_heure($creneau['heure_debut'])); ?></td>
              <td><?php echo admin_e(eillusion_heure($creneau['heure_fin'])); ?></td>
              <td><?php echo (int) $creneau['jauge']; ?></td>
              <td><?php echo (int) $creneau['nb_inscrits']; ?></td>

              <td>
                <?php if ((int) $creneau['places_restantes'] <= 0) { ?>
                  <span class="status-full">0 (Complet)</span>
                <?php } else { ?>
                  <span class="stat-inline"><?php echo (int) $creneau['places_restantes']; ?></span>
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
