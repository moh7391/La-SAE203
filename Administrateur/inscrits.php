<?php
// =====================================================================
//  inscrits.php  -  Liste des inscrits par creneau (espace admin protege).
//  Permet aussi d'ANNULER une inscription.
// =====================================================================

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

// ---------------------------------------------------------------------
//  ANNULATION d'une inscription (POST)
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
    $id = (int) ($_POST['id_inscription'] ?? 0);
    $req = mysqli_prepare($CONNEXION, "DELETE FROM inscription WHERE id_inscription = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $message = "Inscription annulée.";
}

// ---------------------------------------------------------------------
//  RECUPERATION : toutes les inscriptions, regroupees par creneau.
//  On trie par creneau pour pouvoir afficher des sous-titres.
// ---------------------------------------------------------------------
$sql = "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge,
               s.nom_salle,
               i.id_inscription, i.date_inscription,
               p.nom, p.prenom, p.email, p.telephone
        FROM creneau c
        JOIN salle s ON s.id_salle = c.id_salle
        LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
        LEFT JOIN participant p ON p.id_participant = i.id_participant
        ORDER BY c.date_creneau, c.heure_debut, s.nom_salle, p.nom";
$lignes = mysqli_query($CONNEXION, $sql);

// On regroupe les lignes par creneau en memoire pour l'affichage.
$parCreneau = [];
while ($l = mysqli_fetch_assoc($lignes)) {
    $idc = $l['id_creneau'];
    if (!isset($parCreneau[$idc])) {
        $parCreneau[$idc] = [
            'infos'   => $l,   // contient date, heures, salle, jauge
            'inscrits' => [],
        ];
    }
    // Si la ligne a une inscription (LEFT JOIN -> peut etre vide), on l'ajoute.
    if (!empty($l['id_inscription'])) {
        $parCreneau[$idc]['inscrits'][] = $l;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscrits par créneau</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se déconnecter</a></p>
  </header>

  <main>
    <h1>Inscrits par créneau</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <?php foreach ($parCreneau as $bloc) : ?>
      <?php
        $info = $bloc['infos'];
        $dateFr = date('d/m/Y', strtotime($info['date_creneau']));
        $hDebut = substr($info['heure_debut'], 0, 5);
        $hFin   = substr($info['heure_fin'], 0, 5);
        $nb = count($bloc['inscrits']);
      ?>
      <section>
        <h2><?= htmlspecialchars("$dateFr — $hDebut à $hFin — {$info['nom_salle']}") ?></h2>
        <p><?= $nb ?> inscrit(s) sur <?= (int) $info['jauge'] ?> places.</p>

        <?php if ($nb === 0) : ?>
          <p>Aucun inscrit pour ce créneau.</p>
        <?php else : ?>
          <table>
            <caption>Inscrits</caption>
            <thead>
              <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">E-mail</th>
                <th scope="col">Téléphone</th>
                <th scope="col">Inscrit le</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bloc['inscrits'] as $ins) : ?>
                <tr>
                  <td><?= htmlspecialchars($ins['nom']) ?></td>
                  <td><?= htmlspecialchars($ins['prenom']) ?></td>
                  <td><?= htmlspecialchars($ins['email']) ?></td>
                  <td><?= htmlspecialchars($ins['telephone'] ?? '') ?></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($ins['date_inscription']))) ?></td>
                  <td>
                    <form action="inscrits.php" method="post" style="display:inline"
                          onsubmit="return confirm('Annuler cette inscription ?');">
                      <input type="hidden" name="action" value="annuler">
                      <input type="hidden" name="id_inscription" value="<?= (int) $ins['id_inscription'] ?>">
                      <button type="submit">Annuler</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
  </main>
</body>
</html>
