<?php
// Tableau de bord de l'administrateur.

require_once 'verif.php';
require_once 'connexion.php';
require_once 'eillusion-data.php';

// Nombre total de creneaux.
$sqlCreneaux = "SELECT COUNT(*) AS total FROM creneau";
$resultatCreneaux = mysqli_query($CONNEXION, $sqlCreneaux);
$ligneCreneaux = mysqli_fetch_assoc($resultatCreneaux);
$nbCreneaux = (int) $ligneCreneaux['total'];

// Nombre total d'inscriptions.
$sqlInscriptions = "SELECT COUNT(*) AS total FROM inscription";
$resultatInscriptions = mysqli_query($CONNEXION, $sqlInscriptions);
$ligneInscriptions = mysqli_fetch_assoc($resultatInscriptions);
$nbInscriptions = (int) $ligneInscriptions['total'];

// Nombre total de salles.
$sqlSalles = "SELECT COUNT(*) AS total FROM salle";
$resultatSalles = mysqli_query($CONNEXION, $sqlSalles);
$ligneSalles = mysqli_fetch_assoc($resultatSalles);
$nbSalles = (int) $ligneSalles['total'];

// Nombre de creneaux complets.
$sqlComplets = "SELECT COUNT(*) AS total
                FROM (
                    SELECT creneau.id_creneau, creneau.jauge,
                           COUNT(inscription.id_inscription) AS nb_inscrits
                    FROM creneau
                    LEFT JOIN inscription ON inscription.id_creneau = creneau.id_creneau
                    GROUP BY creneau.id_creneau, creneau.jauge
                    HAVING nb_inscrits >= creneau.jauge
                ) AS creneaux_complets";

$resultatComplets = mysqli_query($CONNEXION, $sqlComplets);
$ligneComplets = mysqli_fetch_assoc($resultatComplets);
$nbComplets = (int) $ligneComplets['total'];

// Les 4 dernieres inscriptions.
$sqlDernieres = "SELECT participant.nom, participant.prenom, salle.nom_salle,
                        creneau.heure_debut, inscription.date_inscription
                 FROM inscription
                 JOIN participant ON participant.id_participant = inscription.id_participant
                 JOIN creneau ON creneau.id_creneau = inscription.id_creneau
                 JOIN salle ON salle.id_salle = creneau.id_salle
                 ORDER BY inscription.id_inscription DESC
                 LIMIT 4";

$dernieres = mysqli_query($CONNEXION, $sqlDernieres);

$page_title = 'Tableau de bord - E-LLUSION admin';
$active_page = 'dashboard';
include 'header.php';
?>
<main class="admin-main">
  <section class="admin-page-head">
    <h1 class="admin-title">Tableau de bord</h1>
  </section>

  <section class="stat-grid" aria-label="Statistiques">
    <article class="admin-card stat-card">
      <strong class="stat-number"><?php echo $nbCreneaux; ?></strong>
      <span class="stat-label">Nombre de cr&eacute;neaux</span>
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
      <span class="stat-label">Cr&eacute;neaux complets</span>
    </article>
  </section>

  <nav class="quick-actions" aria-label="Actions rapides">
    <a class="admin-btn" href="creneaux.php">G&eacute;rer les cr&eacute;neaux</a>
    <a class="admin-btn alt" href="inscrits.php">Voir les inscrits</a>
    <a class="admin-btn" href="salles.php">G&eacute;rer les salles</a>
  </nav>

  <section class="admin-card panel">
    <h2>Derni&egrave;res inscriptions</h2>

    <div class="table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Pr&eacute;nom</th>
            <th>Salle</th>
            <th>Cr&eacute;neau</th>
            <th>Statut</th>
          </tr>
        </thead>

        <tbody>
          <?php if (mysqli_num_rows($dernieres) == 0) { ?>
            <tr>
              <td colspan="5">Aucune inscription pour le moment.</td>
            </tr>
          <?php } ?>

          <?php while ($inscription = mysqli_fetch_assoc($dernieres)) { ?>
            <tr>
              <td><?php echo admin_e($inscription['nom']); ?></td>
              <td><?php echo admin_e($inscription['prenom']); ?></td>
              <td><?php echo admin_e($inscription['nom_salle']); ?></td>
              <td><?php echo admin_e(eillusion_heure($inscription['heure_debut'])); ?></td>
              <td><span class="status-valid">Valid&eacute;e</span></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<?php include 'footer.php'; ?>
