<?php
// =====================================================================
//  salles.php  -  Gestion des salles par l'administrateur.
//  On peut : lister, ajouter, modifier et supprimer une salle.
// =====================================================================

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

// ---------- AJOUT / MODIFICATION / SUPPRESSION (POST) ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- Supprimer ---
    if ($action === 'supprimer') {
        $id = (int) $_POST['id_salle'];
        $req = mysqli_prepare($CONNEXION, "DELETE FROM salle WHERE id_salle = ?");
        mysqli_stmt_bind_param($req, "i", $id);
        if (@mysqli_stmt_execute($req)) {
            $message = "Salle supprimee.";
        } else {
            $message = "Suppression impossible : des creneaux utilisent cette salle.";
        }
    }

    // --- Ajouter ou modifier ---
    if ($action === 'ajouter' || $action === 'modifier') {
        $nom         = trim($_POST['nom_salle']);
        $description = trim($_POST['description']);

        if ($nom === '') {
            $message = "Le nom de la salle est obligatoire.";
        } elseif ($action === 'ajouter') {
            $req = mysqli_prepare($CONNEXION, "INSERT INTO salle (nom_salle, description) VALUES (?, ?)");
            mysqli_stmt_bind_param($req, "ss", $nom, $description);
            mysqli_stmt_execute($req);
            $message = "Salle ajoutee.";
        } else { // modifier
            $id = (int) $_POST['id_salle'];
            $req = mysqli_prepare($CONNEXION, "UPDATE salle SET nom_salle = ?, description = ? WHERE id_salle = ?");
            mysqli_stmt_bind_param($req, "ssi", $nom, $description, $id);
            mysqli_stmt_execute($req);
            $message = "Salle modifiee.";
        }
    }
}

// ---------- MODE MODIFICATION : salles.php?modifier=3 ----------
$salleEdit = null;
if (isset($_GET['modifier'])) {
    $id = (int) $_GET['modifier'];
    $req = mysqli_prepare($CONNEXION, "SELECT * FROM salle WHERE id_salle = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $resultat = mysqli_stmt_get_result($req);
    $salleEdit = mysqli_fetch_assoc($resultat);
}

// ---------- LISTE DES SALLES (requete simple) ----------
$salles = mysqli_query($CONNEXION, "SELECT * FROM salle ORDER BY nom_salle");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des salles</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des salles</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <section>
      <?php if ($salleEdit) : ?>
        <h2>Modifier la salle</h2>
      <?php else : ?>
        <h2>Ajouter une salle</h2>
      <?php endif; ?>

      <form action="salles.php" method="post">
        <input type="hidden" name="action" value="<?= $salleEdit ? 'modifier' : 'ajouter' ?>">
        <?php if ($salleEdit) : ?>
          <input type="hidden" name="id_salle" value="<?= $salleEdit['id_salle'] ?>">
        <?php endif; ?>

        <p>
          <label for="nom_salle">Nom de la salle</label><br>
          <input type="text" id="nom_salle" name="nom_salle" required
                 value="<?= htmlspecialchars($salleEdit['nom_salle'] ?? '') ?>">
        </p>
        <p>
          <label for="description">Description</label><br>
          <textarea id="description" name="description" rows="3" cols="40"><?= htmlspecialchars($salleEdit['description'] ?? '') ?></textarea>
        </p>
        <p><button type="submit">Enregistrer</button></p>
      </form>
    </section>

    <section>
      <h2>Liste des salles</h2>
      <table>
        <caption>Salles existantes</caption>
        <thead>
          <tr>
            <th scope="col">Nom</th>
            <th scope="col">Description</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($s = mysqli_fetch_assoc($salles)) : ?>
            <tr>
              <td><?= htmlspecialchars($s['nom_salle']) ?></td>
              <td><?= htmlspecialchars($s['description']) ?></td>
              <td>
                <a href="salles.php?modifier=<?= $s['id_salle'] ?>">Modifier</a>
                <form action="salles.php" method="post"
                      onsubmit="return confirm('Supprimer cette salle ?');">
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id_salle" value="<?= $s['id_salle'] ?>">
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
