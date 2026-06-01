<?php
// =====================================================================
//  salles.php  -  CRUD des salles (espace admin protege).
//  Lister / Creer / Modifier / Supprimer une salle.
// =====================================================================

require_once 'verif.php';
require_once 'connexion.php';

$message = "";

// ---------------------------------------------------------------------
//  TRAITEMENT DES ACTIONS (POST)
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- SUPPRESSION ---
    if ($action === 'supprimer') {
        $id = (int) ($_POST['id_salle'] ?? 0);
        $req = mysqli_prepare($CONNEXION, "DELETE FROM salle WHERE id_salle = ?");
        mysqli_stmt_bind_param($req, "i", $id);
        // Echoue si des creneaux ou des elements sont lies a la salle (cle etrangere).
        if (@mysqli_stmt_execute($req)) {
            $message = "Salle supprimée.";
        } else {
            $message = "Impossible de supprimer : des créneaux (ou éléments) utilisent cette salle.";
        }
    }

    // --- CREATION / MODIFICATION ---
    if ($action === 'creer' || $action === 'modifier') {
        $nom         = trim($_POST['nom_salle'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($nom === '') {
            $message = "Le nom de la salle est obligatoire.";
        } else {
            if ($action === 'creer') {
                $req = mysqli_prepare($CONNEXION,
                    "INSERT INTO salle (nom_salle, description) VALUES (?, ?)");
                mysqli_stmt_bind_param($req, "ss", $nom, $description);
                mysqli_stmt_execute($req);
                $message = "Salle créée.";
            } else { // modifier
                $id = (int) ($_POST['id_salle'] ?? 0);
                $req = mysqli_prepare($CONNEXION,
                    "UPDATE salle SET nom_salle = ?, description = ? WHERE id_salle = ?");
                mysqli_stmt_bind_param($req, "ssi", $nom, $description, $id);
                mysqli_stmt_execute($req);
                $message = "Salle modifiée.";
            }
        }
    }
}

// ---------------------------------------------------------------------
//  MODE EDITION : ?action=edit&id=
// ---------------------------------------------------------------------
$salleEdit = null;
if (($_GET['action'] ?? '') === 'edit') {
    $id = (int) ($_GET['id'] ?? 0);
    $req = mysqli_prepare($CONNEXION, "SELECT * FROM salle WHERE id_salle = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $salleEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($req));
}

// Liste des salles (avec nombre de creneaux rattaches, pour info)
$sql = "SELECT s.id_salle, s.nom_salle, s.description, COUNT(c.id_creneau) AS nb_creneaux
        FROM salle s
        LEFT JOIN creneau c ON c.id_salle = s.id_salle
        GROUP BY s.id_salle, s.nom_salle, s.description
        ORDER BY s.nom_salle";
$salles = mysqli_query($CONNEXION, $sql);
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
    <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se déconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des salles</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <section>
      <h2><?= $salleEdit ? "Modifier la salle" : "Ajouter une salle" ?></h2>
      <form action="salles.php" method="post">
        <input type="hidden" name="action" value="<?= $salleEdit ? 'modifier' : 'creer' ?>">
        <?php if ($salleEdit) : ?>
          <input type="hidden" name="id_salle" value="<?= (int) $salleEdit['id_salle'] ?>">
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
        <p>
          <button type="submit"><?= $salleEdit ? "Enregistrer les modifications" : "Ajouter" ?></button>
          <?php if ($salleEdit) : ?>
            <a href="salles.php">Annuler</a>
          <?php endif; ?>
        </p>
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
            <th scope="col">Créneaux</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($s = mysqli_fetch_assoc($salles)) : ?>
            <tr>
              <td><?= htmlspecialchars($s['nom_salle']) ?></td>
              <td><?= htmlspecialchars($s['description'] ?? '') ?></td>
              <td><?= (int) $s['nb_creneaux'] ?></td>
              <td>
                <a href="salles.php?action=edit&amp;id=<?= (int) $s['id_salle'] ?>">Modifier</a>
                <form action="salles.php" method="post" style="display:inline"
                      onsubmit="return confirm('Supprimer cette salle ?');">
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id_salle" value="<?= (int) $s['id_salle'] ?>">
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
