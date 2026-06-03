<?php
// Tableau de bord de l'administrateur.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';
require_once 'admin-layout.php';

$nbCreneaux = (int) mysqli_fetch_assoc(mysqli_query($CONNEXION, "SELECT COUNT(*) AS total FROM creneau"))['total'];
$nbInscriptions = (int) mysqli_fetch_assoc(mysqli_query($CONNEXION, "SELECT COUNT(*) AS total FROM inscription"))['total'];
$nbSalles = (int) mysqli_fetch_assoc(mysqli_query($CONNEXION, "SELECT COUNT(*) AS total FROM salle"))['total'];

$resComplets = mysqli_query($CONNEXION,
    "SELECT COUNT(*) AS total
     FROM (
        SELECT creneau.id_creneau, creneau.jauge, COUNT(inscription.id_inscription) AS nb_inscrits
        FROM creneau
        LEFT JOIN inscription ON inscription.id_creneau = creneau.id_creneau
        GROUP BY creneau.id_creneau, creneau.jauge
        HAVING nb_inscrits >= creneau.jauge
     ) AS complets");
$nbComplets = (int) mysqli_fetch_assoc($resComplets)['total'];

$dernieres = mysqli_query($CONNEXION,
    "SELECT participant.nom, participant.prenom, salle.nom_salle,
            creneau.heure_debut, inscription.date_inscription
     FROM inscription
     JOIN participant ON participant.id_participant = inscription.id_participant
     JOIN creneau ON creneau.id_creneau = inscription.id_creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     ORDER BY inscription.id_inscription DESC
     LIMIT 4");

admin_page_start('Tableau de bord', 'dashboard');
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Tableau de bord</h1>
  </section>

  <section class="stat-grid" aria-label="Statistiques">
    <article class="admin-card stat-card">
      <strong class="stat-number"><?php echo $nbCreneaux; ?></strong>
      <span class="stat-label">Nombre de créneaux</span>
    </article>
    <article class="admin-card stat-card">
      <strong class="stat-number"><?php echo $nbInscriptions; ?></strong>
      <span class="stat-label">Nombre d'inscriptions</span>
    </article>
    <article class="admin-card stat-card">
      <strong class="stat-number"><?php echo $nbSalles; ?></strong>
      <span class="stat-label">Nombre de salles</span>
    </article>
    <article class="admin-card stat-card">
      <strong class="stat-number"><?php echo $nbComplets; ?></strong>
      <span class="stat-label">Créneaux complets</span>
    </article>
  </section>

  <nav class="quick-actions" aria-label="Actions rapides">
    <a class="admin-btn" href="creneaux.php">Gérer les créneaux</a>
    <a class="admin-btn alt" href="inscrits.php">Voir les inscrits</a>
    <a class="admin-btn" href="salles.php">Gérer les salles</a>
  </nav>

  <section class="admin-card panel">
    <h2>Dernières inscriptions</h2>
    <div class="table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Salle</th>
            <th>Créneau</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($dernieres) === 0) { ?>
            <tr>
              <td colspan="5">Aucune inscription pour le moment.</td>
            </tr>
          <?php } ?>
          <?php while ($i = mysqli_fetch_assoc($dernieres)) { ?>
            <tr>
              <td><?php echo admin_e($i['nom']); ?></td>
              <td><?php echo admin_e($i['prenom']); ?></td>
              <td><?php echo admin_e($i['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_heure($i['heure_debut'])); ?></td>
              <td><span class="status-valid">Validée</span></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<?php admin_footer(); ?>
