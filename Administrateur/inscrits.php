<?php
// Liste des inscriptions.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['id_inscription'])) {
    $id = (int) $_POST['id_inscription'];
    mysqli_query($CONNEXION, "DELETE FROM inscription WHERE id_inscription = $id");
    $message = "Inscription annulée.";
}

$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';
$filtreSalle = isset($_GET['salle']) ? (int) $_GET['salle'] : 0;
$filtreDate = isset($_GET['date']) ? $_GET['date'] : '';
$filtreStatut = isset($_GET['statut']) ? $_GET['statut'] : '';

$where = array();
if ($recherche !== '') {
    $q = mysqli_real_escape_string($CONNEXION, $recherche);
    $where[] = "(participant.nom LIKE '%$q%' OR participant.prenom LIKE '%$q%' OR participant.email LIKE '%$q%')";
}
if ($filtreSalle > 0) {
    $where[] = "salle.id_salle = $filtreSalle";
}
if ($filtreDate !== '') {
    $date = mysqli_real_escape_string($CONNEXION, $filtreDate);
    $where[] = "creneau.date_creneau = '$date'";
}

$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$salles = eillusion_db_salles($CONNEXION);
$inscrits = mysqli_query($CONNEXION,
    "SELECT inscription.id_inscription, participant.nom, participant.prenom,
            participant.email, participant.telephone, salle.nom_salle,
            creneau.date_creneau, creneau.heure_debut
     FROM inscription
     JOIN participant ON participant.id_participant = inscription.id_participant
     JOIN creneau ON creneau.id_creneau = inscription.id_creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     $whereSql
     ORDER BY inscription.id_inscription DESC");

$page_title = 'Liste des inscrits - E-LLUSION admin';
$active_page = 'inscrits';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Liste des inscrits</h1>
  </section>

  <?php if ($message != "") { ?>
    <p class="admin-message"><?php echo admin_e($message); ?></p>
  <?php } ?>

  <form class="admin-card filter-card" method="get" action="inscrits.php">
    <div class="filter-grid four">
      <div class="field">
        <label for="q">Rechercher un participant</label>
        <input type="search" id="q" name="q" placeholder="Nom, prénom, email..." value="<?php echo admin_e($recherche); ?>">
      </div>

      <div class="field">
        <label for="salle">Filtrer par salle</label>
        <select id="salle" name="salle" onchange="this.form.submit()">
          <option value="">Toutes</option>
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

      <div class="field">
        <label for="statut">Filtrer par statut</label>
        <select id="statut" name="statut" onchange="this.form.submit()">
          <option value="">Tous</option>
          <option value="validee"<?php if ($filtreStatut === 'validee') echo ' selected'; ?>>Validée</option>
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
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Salle</th>
            <th>Créneau</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$inscrits || mysqli_num_rows($inscrits) === 0) { ?>
            <tr>
              <td colspan="8">Aucun inscrit trouvé.</td>
            </tr>
          <?php } ?>
          <?php while ($i = mysqli_fetch_assoc($inscrits)) { ?>
            <tr>
              <td><?php echo admin_e($i['nom']); ?></td>
              <td><?php echo admin_e($i['prenom']); ?></td>
              <td class="email-cell"><?php echo admin_e($i['email']); ?></td>
              <td><?php echo admin_e($i['telephone']); ?></td>
              <td><?php echo admin_e($i['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_heure($i['heure_debut'])); ?></td>
              <td><span class="status-valid">Validée</span></td>
              <td>
                <div class="btn-row">
                  <a class="admin-pill" href="inscrits.php">Modifier</a>
                  <form action="inscrits.php" method="post" onsubmit="return confirm('Annuler cette inscription ?');">
                    <input type="hidden" name="id_inscription" value="<?php echo (int) $i['id_inscription']; ?>">
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
