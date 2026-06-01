<?php
// =====================================================================
//  creneaux.php  -  Gestion des creneaux par l'administrateur.
//  On peut : lister, ajouter, modifier et supprimer un creneau.
// =====================================================================

require_once 'verif.php';      // protege la page (redirige si non connecte)
require_once 'connexion.php';  // $CONNEXION

$message = "";

// ---------- AJOUT / MODIFICATION / SUPPRESSION (formulaires POST) ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- Supprimer un creneau ---
    if ($action === 'supprimer') {
        $id = (int) $_POST['id_creneau'];
        $req = mysqli_prepare($CONNEXION, "DELETE FROM creneau WHERE id_creneau = ?");
        mysqli_stmt_bind_param($req, "i", $id);
        // La suppression echoue si le creneau a des inscriptions (cle etrangere).
        if (@mysqli_stmt_execute($req)) {
            $message = "Creneau supprime.";
        } else {
            $message = "Suppression impossible : ce creneau a des inscriptions.";
        }
    }

    // --- Ajouter ou modifier un creneau ---
    if ($action === 'ajouter' || $action === 'modifier') {
        $date    = $_POST['date_creneau'];
        $debut   = $_POST['heure_debut'];
        $fin     = $_POST['heure_fin'];
        $jauge   = (int) $_POST['jauge'];
        $idSalle = (int) $_POST['id_salle'];
        $idAdmin = $_SESSION['admin_id'];

        if ($date === '' || $debut === '' || $fin === '' || $jauge <= 0 || $idSalle <= 0) {
            $message = "Veuillez remplir tous les champs.";
        } elseif ($action === 'ajouter') {
            $req = mysqli_prepare($CONNEXION,
                "INSERT INTO creneau (date_creneau, heure_debut, heure_fin, jauge, id_admin, id_salle)
                 VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($req, "sssiii", $date, $debut, $fin, $jauge, $idAdmin, $idSalle);
            mysqli_stmt_execute($req);
            $message = "Creneau ajoute.";
        } else { // modifier
            $id = (int) $_POST['id_creneau'];
            $req = mysqli_prepare($CONNEXION,
                "UPDATE creneau SET date_creneau = ?, heure_debut = ?, heure_fin = ?, jauge = ?, id_salle = ?
                 WHERE id_creneau = ?");
            mysqli_stmt_bind_param($req, "sssiii", $date, $debut, $fin, $jauge, $idSalle, $id);
            mysqli_stmt_execute($req);
            $message = "Creneau modifie.";
        }
    }
}

// ---------- MODE MODIFICATION : on charge le creneau a modifier ----------
// Si l'URL est creneaux.php?modifier=12, on remplit le formulaire avec ce creneau.
$creneauEdit = null;
if (isset($_GET['modifier'])) {
    $id = (int) $_GET['modifier'];
    $req = mysqli_prepare($CONNEXION, "SELECT * FROM creneau WHERE id_creneau = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $resultat = mysqli_stmt_get_result($req);
    $creneauEdit = mysqli_fetch_assoc($resultat);
}

// ---------- DONNEES POUR L'AFFICHAGE (requetes simples) ----------
$salles = mysqli_query($CONNEXION, "SELECT id_salle, nom_salle FROM salle ORDER BY nom_salle");

$creneaux = mysqli_query($CONNEXION,
    "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge,
            s.nom_salle, COUNT(i.id_inscription) AS nb_inscrits
     FROM creneau c
     JOIN salle s ON s.id_salle = c.id_salle
     LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
     GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge, s.nom_salle
     ORDER BY c.date_creneau, c.heure_debut, s.nom_salle");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des creneaux</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des creneaux</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <!-- Formulaire d'ajout / modification -->
    <section>
      <?php if ($creneauEdit) : ?>
        <h2>Modifier le creneau</h2>
      <?php else : ?>
        <h2>Ajouter un creneau</h2>
      <?php endif; ?>

      <form action="creneaux.php" method="post">
        <!-- action = "modifier" si on modifie, sinon "ajouter" -->
        <input type="hidden" name="action" value="<?= $creneauEdit ? 'modifier' : 'ajouter' ?>">
        <?php if ($creneauEdit) : ?>
          <input type="hidden" name="id_creneau" value="<?= $creneauEdit['id_creneau'] ?>">
        <?php endif; ?>

        <p>
          <label for="date_creneau">Date</label><br>
          <input type="date" id="date_creneau" name="date_creneau" required
                 value="<?= htmlspecialchars($creneauEdit['date_creneau'] ?? '') ?>">
        </p>
        <p>
          <label for="heure_debut">Heure de debut</label><br>
          <input type="time" id="heure_debut" name="heure_debut" required
                 value="<?= htmlspecialchars($creneauEdit['heure_debut'] ?? '') ?>">
        </p>
        <p>
          <label for="heure_fin">Heure de fin</label><br>
          <input type="time" id="heure_fin" name="heure_fin" required
                 value="<?= htmlspecialchars($creneauEdit['heure_fin'] ?? '') ?>">
        </p>
        <p>
          <label for="jauge">Nombre de places</label><br>
          <input type="number" id="jauge" name="jauge" min="1" required
                 value="<?= htmlspecialchars($creneauEdit['jauge'] ?? '12') ?>">
        </p>
        <p>
          <label for="id_salle">Salle</label><br>
          <select id="id_salle" name="id_salle" required>
            <option value="">-- Choisir une salle --</option>
            <?php while ($s = mysqli_fetch_assoc($salles)) : ?>
              <option value="<?= $s['id_salle'] ?>"
                <?php if ($creneauEdit && $creneauEdit['id_salle'] == $s['id_salle']) echo 'selected'; ?>>
                <?= htmlspecialchars($s['nom_salle']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </p>
        <p><button type="submit">Enregistrer</button></p>
      </form>
    </section>

    <!-- Liste des creneaux -->
    <section>
      <h2>Liste des creneaux</h2>
      <table>
        <caption>Creneaux existants</caption>
        <thead>
          <tr>
            <th scope="col">Date</th>
            <th scope="col">Horaire</th>
            <th scope="col">Salle</th>
            <th scope="col">Inscrits / Places</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($c = mysqli_fetch_assoc($creneaux)) : ?>
            <?php
              $date = date('d/m/Y', strtotime($c['date_creneau']));
              $debut = substr($c['heure_debut'], 0, 5);
              $fin   = substr($c['heure_fin'], 0, 5);
            ?>
            <tr>
              <td><?= htmlspecialchars($date) ?></td>
              <td><?= htmlspecialchars("$debut a $fin") ?></td>
              <td><?= htmlspecialchars($c['nom_salle']) ?></td>
              <td><?= $c['nb_inscrits'] ?> / <?= $c['jauge'] ?></td>
              <td>
                <a href="creneaux.php?modifier=<?= $c['id_creneau'] ?>">Modifier</a>
                <form action="creneaux.php" method="post"
                      onsubmit="return confirm('Supprimer ce creneau ?');">
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id_creneau" value="<?= $c['id_creneau'] ?>">
                  <button type="submit">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
