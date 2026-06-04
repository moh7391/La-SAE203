<?php
// Page administrateur : liste des salles et des elements d'exposition.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

$salles = array();

$sqlSalles = "SELECT salle.id_salle, salle.nom_salle, salle.description,
                     COUNT(contenir.id_element) AS nb_elements
              FROM salle
              LEFT JOIN contenir ON contenir.id_salle = salle.id_salle
              GROUP BY salle.id_salle, salle.nom_salle, salle.description
              ORDER BY salle.nom_salle";

$resultatSalles = mysqli_query($CONNEXION, $sqlSalles);

while ($salle = mysqli_fetch_assoc($resultatSalles)) {
    $salles[] = $salle;
}

$sqlElements = "SELECT element_expo.titre, element_expo.description, salle.nom_salle
                FROM element_expo
                JOIN contenir ON contenir.id_element = element_expo.id_element
                JOIN salle ON salle.id_salle = contenir.id_salle
                ORDER BY salle.nom_salle, element_expo.titre";

$elements = mysqli_query($CONNEXION, $sqlElements);

$page_title = 'Gestion des salles - E-LLUSION admin';
$active_page = 'salles';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Gestion des salles</h1>
  </section>

  <section class="room-section">
    <div class="room-grid-admin">
      <?php foreach ($salles as $salle) { ?>
        <?php
        $codeSalle = preg_replace('/\D/', '', $salle['nom_salle']);
        $titreSalle = $salle['nom_salle'];

        if ($codeSalle != '') {
            $titreSalle = 'Salle ' . str_pad($codeSalle, 3, '0', STR_PAD_LEFT);
        }
        ?>

        <article class="admin-card room-card-admin">
          <span class="room-dot" aria-hidden="true"></span>

          <h2><?php echo admin_e($titreSalle); ?></h2>
          <p><?php echo admin_e($salle['description']); ?></p>

          <div class="room-meta">
            <span class="room-count">
              <strong><?php echo (int) $salle['nb_elements']; ?></strong>
              &eacute;l&eacute;ments d'exposition
            </span>

            <a class="admin-btn small" href="salles.php">Voir / Modifier</a>
          </div>
        </article>
      <?php } ?>
    </div>
  </section>

  <section class="admin-card panel">
    <h2>&Eacute;l&eacute;ments d'exposition</h2>

    <div class="table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Salle</th>
            <th>Description courte</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!$elements || mysqli_num_rows($elements) == 0) { ?>
            <tr>
              <td colspan="4">Aucun &eacute;l&eacute;ment d'exposition trouv&eacute;.</td>
            </tr>
          <?php } ?>

          <?php while ($element = mysqli_fetch_assoc($elements)) { ?>
            <tr>
              <td><?php echo admin_e($element['titre']); ?></td>
              <td><?php echo admin_e($element['nom_salle']); ?></td>
              <td class="description-cell"><?php echo admin_e($element['description']); ?></td>

              <td>
                <div class="btn-row">
                  <a class="admin-pill" href="salles.php">Modifier</a>
                  <a class="admin-danger" href="salles.php">Supprimer</a>
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
