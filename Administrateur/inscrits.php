<?php
// Page administrateur : liste des inscriptions.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_inscription'])) {
    $idInscription = (int) $_POST['id_inscription'];

    $sqlSuppression = "DELETE FROM inscription
                       WHERE id_inscription = $idInscription";

    mysqli_query($CONNEXION, $sqlSuppression);
    $message = 'Inscription annulee.';
}

$recherche = '';
$filtreSalle = 0;
$filtreDate = '';
$filtreStatut = '';

if (isset($_GET['q'])) {
    $recherche = trim($_GET['q']);
}

if (isset($_GET['salle'])) {
    $filtreSalle = (int) $_GET['salle'];
}

if (isset($_GET['date'])) {
    $filtreDate = $_GET['date'];
}

if (isset($_GET['statut'])) {
    $filtreStatut = $_GET['statut'];
}

// On construit la partie WHERE petit a petit.
$conditions = array();

if ($recherche != '') {
    $rechercheSql = mysqli_real_escape_string($CONNEXION, $recherche);

    $conditions[] = "(participant.nom LIKE '%$rechercheSql%'
                      OR participant.prenom LIKE '%$rechercheSql%'
                      OR participant.email LIKE '%$rechercheSql%')";
}

if ($filtreSalle > 0) {
    $conditions[] = "salle.id_salle = $filtreSalle";
}

if ($filtreDate != '') {
    $dateSql = mysqli_real_escape_string($CONNEXION, $filtreDate);
    $conditions[] = "creneau.date_creneau = '$dateSql'";
}

$whereSql = '';

if (count($conditions) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $conditions);
}

$salles = eillusion_db_salles($CONNEXION);

$sqlInscrits = "SELECT inscription.id_inscription, participant.nom, participant.prenom,
                       participant.email, participant.telephone, salle.nom_salle,
                       creneau.date_creneau, creneau.heure_debut
                FROM inscription
                JOIN participant ON participant.id_participant = inscription.id_participant
                JOIN creneau ON creneau.id_creneau = inscription.id_creneau
                JOIN salle ON salle.id_salle = creneau.id_salle
                $whereSql
                ORDER BY inscription.id_inscription DESC";

$inscrits = mysqli_query($CONNEXION, $sqlInscrits);

$page_title = 'Liste des inscrits - E-LLUSION admin';
$active_page = 'inscrits';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Liste des inscrits</h1>
  </section>

  <?php if ($message != '') { ?>
    <p class="admin-message"><?php echo admin_e($message); ?></p>
  <?php } ?>

  <form class="admin-card filter-card" method="get" action="inscrits.php">
    <div class="filter-grid four">
      <div class="field">
        <label for="q">Rechercher un participant</label>
        <input type="search" id="q" name="q" placeholder="Nom, pr&eacute;nom, email..." value="<?php echo admin_e($recherche); ?>">
      </div>

      <div class="field">
        <label for="salle">Filtrer par salle</label>

        <select id="salle" name="salle" onchange="this.form.submit()">
          <option value="">Toutes</option>

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

      <div class="field">
        <label for="statut">Filtrer par statut</label>

        <select id="statut" name="statut" onchange="this.form.submit()">
          <option value="">Tous</option>
          <option value="validee" <?php if ($filtreStatut == 'validee') { echo 'selected'; } ?>>Valid&eacute;e</option>
        </select>
      </div>
    </div>
  </form>

  <section class="admin-card panel">
    <div class="table-scroll">
      <table class="admin-table compact-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Pr&eacute;nom</th>
            <th>Email</th>
            <th>T&eacute;l&eacute;phone</th>
            <th>Salle</th>
            <th>Cr&eacute;neau</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!$inscrits || mysqli_num_rows($inscrits) == 0) { ?>
            <tr>
              <td colspan="8">Aucun inscrit trouv&eacute;.</td>
            </tr>
          <?php } ?>

          <?php while ($inscrit = mysqli_fetch_assoc($inscrits)) { ?>
            <tr>
              <td><?php echo admin_e($inscrit['nom']); ?></td>
              <td><?php echo admin_e($inscrit['prenom']); ?></td>
              <td class="email-cell"><?php echo admin_e($inscrit['email']); ?></td>
              <td><?php echo admin_e($inscrit['telephone']); ?></td>
              <td><?php echo admin_e($inscrit['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_heure($inscrit['heure_debut'])); ?></td>
              <td><span class="status-valid">Valid&eacute;e</span></td>

              <td>
                <div class="btn-row">
                  <a class="admin-pill" href="inscrits.php">Modifier</a>

                  <form action="inscrits.php" method="post" onsubmit="return confirm('Annuler cette inscription ?');">
                    <input type="hidden" name="id_inscription" value="<?php echo (int) $inscrit['id_inscription']; ?>">
                    <button class="admin-danger" type="submit">Annuler</button>
                  </form>
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
