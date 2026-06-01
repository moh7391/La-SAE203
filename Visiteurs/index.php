<?php
// =====================================================================
//  index.php  -  Page d'accueil (public) : liste des creneaux.
//  Pour chaque creneau : date, horaires, salle, places restantes
//  (= jauge - nombre d'inscriptions) et un lien pour s'inscrire.
// =====================================================================

require_once 'connexion.php';

// Requete : on relie creneau + salle, et on compte les inscriptions
// pour calculer les places restantes. LEFT JOIN pour garder les
// creneaux sans aucune inscription (COUNT = 0).
$sql = "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin,
               s.nom_salle, c.jauge,
               (c.jauge - COUNT(i.id_inscription)) AS places_restantes
        FROM creneau c
        JOIN salle s ON s.id_salle = c.id_salle
        LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
        GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle, c.jauge
        ORDER BY c.date_creneau, c.heure_debut, s.nom_salle";
$creneaux = mysqli_query($CONNEXION, $sql);

require_once 'header.php';
?>

  <main>
    <h1>Créneaux d'inscription à l'exposition</h1>
    <p>Choisissez un créneau disponible puis cliquez sur « S'inscrire ».</p>

    <table>
      <caption>Liste des créneaux et places restantes</caption>
      <thead>
        <tr>
          <th scope="col">Date</th>
          <th scope="col">Horaire</th>
          <th scope="col">Salle</th>
          <th scope="col">Places restantes</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($c = mysqli_fetch_assoc($creneaux)) : ?>
          <?php
            $dateFr = date('d/m/Y', strtotime($c['date_creneau']));
            $hDebut = substr($c['heure_debut'], 0, 5);
            $hFin   = substr($c['heure_fin'], 0, 5);
            $complet = ($c['places_restantes'] <= 0);
          ?>
          <tr>
            <td><?= htmlspecialchars($dateFr) ?></td>
            <td><?= htmlspecialchars("$hDebut à $hFin") ?></td>
            <td><?= htmlspecialchars($c['nom_salle']) ?></td>
            <td><?= (int) $c['places_restantes'] ?> / <?= (int) $c['jauge'] ?></td>
            <td>
              <?php if ($complet) : ?>
                <span>Complet</span>
              <?php else : ?>
                <a href="inscription.php?id_creneau=<?= (int) $c['id_creneau'] ?>">S'inscrire</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </main>

<?php require_once 'footer.php'; ?>
