<?php
// =====================================================================
//  inscrits.php  -  Liste des inscrits, creneau par creneau (admin).
//  Permet aussi d'ANNULER une inscription.
// =====================================================================

require_once 'verif.php';      // protege la page (redirige si non connecte)
require_once 'connexion.php';  // $CONNEXION

$message = "";

// --- ANNULATION d'une inscription ---
// On utilise une requete preparee car l'id vient de l'utilisateur.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $id = (int) $_POST['id_inscription'];
    $req = mysqli_prepare($CONNEXION, "DELETE FROM inscription WHERE id_inscription = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $message = "Inscription annulee.";
}

// --- On recupere tous les creneaux (requete simple : pas de donnee utilisateur) ---
$creneaux = mysqli_query($CONNEXION,
    "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge, s.nom_salle
     FROM creneau c
     JOIN salle s ON s.id_salle = c.id_salle
     ORDER BY c.date_creneau, c.heure_debut, s.nom_salle");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscrits par creneau</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Inscrits par creneau</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <?php
    // Pour chaque creneau, on affiche ses inscrits.
    while ($c = mysqli_fetch_assoc($creneaux)) :

        $dateFr = date('d/m/Y', strtotime($c['date_creneau']));
        $hDebut = substr($c['heure_debut'], 0, 5);
        $hFin   = substr($c['heure_fin'], 0, 5);

        // On va chercher les inscrits de CE creneau (id du creneau = donnee -> requete preparee).
        $req = mysqli_prepare($CONNEXION,
            "SELECT i.id_inscription, p.nom, p.prenom, p.email, p.telephone, i.date_inscription
             FROM inscription i
             JOIN participant p ON p.id_participant = i.id_participant
             WHERE i.id_creneau = ?
             ORDER BY p.nom");
        mysqli_stmt_bind_param($req, "i", $c['id_creneau']);
        mysqli_stmt_execute($req);
        $inscrits = mysqli_stmt_get_result($req);
        $nb = mysqli_num_rows($inscrits);
    ?>
      <section>
        <h2><?= htmlspecialchars("$dateFr — $hDebut a $hFin — {$c['nom_salle']}") ?></h2>
        <p><?= $nb ?> inscrit(s) sur <?= $c['jauge'] ?> places.</p>

        <?php if ($nb == 0) : ?>
          <p>Aucun inscrit pour ce creneau.</p>
        <?php else : ?>
          <table>
            <caption>Inscrits</caption>
            <thead>
              <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prenom</th>
                <th scope="col">E-mail</th>
                <th scope="col">Telephone</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($ins = mysqli_fetch_assoc($inscrits)) : ?>
                <tr>
                  <td><?= htmlspecialchars($ins['nom']) ?></td>
                  <td><?= htmlspecialchars($ins['prenom']) ?></td>
                  <td><?= htmlspecialchars($ins['email']) ?></td>
                  <td><?= htmlspecialchars($ins['telephone']) ?></td>
                  <td>
                    <form action="inscrits.php" method="post"
                          onsubmit="return confirm('Annuler cette inscription ?');">
                      <input type="hidden" name="id_inscription" value="<?= $ins['id_inscription'] ?>">
                      <button type="submit">Annuler</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    <?php endwhile; ?>
  </main>
</body>
</html>
