<?php
// =====================================================================
//  creneaux.php  -  CRUD des creneaux (espace admin protege).
//  - Lister les creneaux (avec salle + nombre d'inscrits)
//  - Creer / Modifier / Supprimer un creneau
//  Tout en requetes preparees (securite).
// =====================================================================

require_once 'verif.php';     // protege la page
require_once 'connexion.php'; // $CONNEXION

$message = "";   // message de retour (succes ou erreur)

// ---------------------------------------------------------------------
//  TRAITEMENT DES ACTIONS (POST) : creation, modification, suppression
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- SUPPRESSION ---
    if ($action === 'supprimer') {
        $id = (int) ($_POST['id_creneau'] ?? 0);
        $req = mysqli_prepare($CONNEXION, "DELETE FROM creneau WHERE id_creneau = ?");
        mysqli_stmt_bind_param($req, "i", $id);
        // La suppression echoue si des inscriptions existent (cle etrangere).
        if (@mysqli_stmt_execute($req)) {
            $message = "Créneau supprimé.";
        } else {
            $message = "Impossible de supprimer : ce créneau a des inscriptions. Annulez-les d'abord.";
        }
    }

    // --- CREATION ou MODIFICATION ---
    if ($action === 'creer' || $action === 'modifier') {
        $date    = $_POST['date_creneau'] ?? '';
        $hDebut  = $_POST['heure_debut'] ?? '';
        $hFin    = $_POST['heure_fin'] ?? '';
        $jauge   = (int) ($_POST['jauge'] ?? 0);
        $idSalle = (int) ($_POST['id_salle'] ?? 0);
        $idAdmin = $_SESSION['admin_id'];

        // Validation simple
        if ($date === '' || $hDebut === '' || $hFin === '' || $jauge <= 0 || $idSalle <= 0) {
            $message = "Veuillez remplir tous les champs correctement.";
        } elseif ($hFin <= $hDebut) {
            $message = "L'heure de fin doit être après l'heure de début.";
        } else {
            if ($action === 'creer') {
                $req = mysqli_prepare($CONNEXION,
                    "INSERT INTO creneau (date_creneau, heure_debut, heure_fin, jauge, id_admin, id_salle)
                     VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($req, "sssiii", $date, $hDebut, $hFin, $jauge, $idAdmin, $idSalle);
                mysqli_stmt_execute($req);
                $message = "Créneau créé.";
            } else { // modifier
                $id = (int) ($_POST['id_creneau'] ?? 0);
                $req = mysqli_prepare($CONNEXION,
                    "UPDATE creneau SET date_creneau = ?, heure_debut = ?, heure_fin = ?, jauge = ?, id_salle = ?
                     WHERE id_creneau = ?");
                mysqli_stmt_bind_param($req, "sssiii", $date, $hDebut, $hFin, $jauge, $idSalle, $id);
                mysqli_stmt_execute($req);
                $message = "Créneau modifié.";
            }
        }
    }
}

// ---------------------------------------------------------------------
//  MODE EDITION : si ?action=edit&id=, on charge le creneau a modifier
// ---------------------------------------------------------------------
$creneauEdit = null;
if (($_GET['action'] ?? '') === 'edit') {
    $id = (int) ($_GET['id'] ?? 0);
    $req = mysqli_prepare($CONNEXION, "SELECT * FROM creneau WHERE id_creneau = ?");
    mysqli_stmt_bind_param($req, "i", $id);
    mysqli_stmt_execute($req);
    $creneauEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($req));
}

// ---------------------------------------------------------------------
//  DONNEES POUR L'AFFICHAGE
// ---------------------------------------------------------------------
// Liste des salles (pour le menu deroulant du formulaire)
$salles = mysqli_query($CONNEXION, "SELECT id_salle, nom_salle FROM salle ORDER BY nom_salle");

// Liste des creneaux + salle + nombre d'inscrits
$sql = "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge,
               s.nom_salle, COUNT(i.id_inscription) AS nb_inscrits
        FROM creneau c
        JOIN salle s ON s.id_salle = c.id_salle
        LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
        GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, c.jauge, s.nom_salle
        ORDER BY c.date_creneau, c.heure_debut, s.nom_salle";
$creneaux = mysqli_query($CONNEXION, $sql);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des créneaux</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="accueil.php">Tableau de bord</a> — <a href="deconnexion.php">Se déconnecter</a></p>
  </header>

  <main>
    <h1>Gestion des créneaux</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <!-- ============ FORMULAIRE CREER / MODIFIER ============ -->
    <section>
      <h2><?= $creneauEdit ? "Modifier le créneau" : "Ajouter un créneau" ?></h2>
      <form action="creneaux.php" method="post">
        <input type="hidden" name="action" value="<?= $creneauEdit ? 'modifier' : 'creer' ?>">
        <?php if ($creneauEdit) : ?>
          <input type="hidden" name="id_creneau" value="<?= (int) $creneauEdit['id_creneau'] ?>">
        <?php endif; ?>

        <p>
          <label for="date_creneau">Date</label><br>
          <input type="date" id="date_creneau" name="date_creneau" required
                 value="<?= htmlspecialchars($creneauEdit['date_creneau'] ?? '') ?>">
        </p>
        <p>
          <label for="heure_debut">Heure de début</label><br>
          <input type="time" id="heure_debut" name="heure_debut" required
                 value="<?= htmlspecialchars($creneauEdit['heure_debut'] ?? '') ?>">
        </p>
        <p>
          <label for="heure_fin">Heure de fin</label><br>
          <input type="time" id="heure_fin" name="heure_fin" required
                 value="<?= htmlspecialchars($creneauEdit['heure_fin'] ?? '') ?>">
        </p>
        <p>
          <label for="jauge">Jauge (places)</label><br>
          <input type="number" id="jauge" name="jauge" min="1" required
                 value="<?= htmlspecialchars($creneauEdit['jauge'] ?? '12') ?>">
        </p>
        <p>
          <label for="id_salle">Salle</label><br>
          <select id="id_salle" name="id_salle" required>
            <option value="">-- Choisir une salle --</option>
            <?php while ($s = mysqli_fetch_assoc($salles)) : ?>
              <?php $sel = ($creneauEdit && $creneauEdit['id_salle'] == $s['id_salle']) ? 'selected' : ''; ?>
              <option value="<?= (int) $s['id_salle'] ?>" <?= $sel ?>>
                <?= htmlspecialchars($s['nom_salle']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </p>
        <p>
          <button type="submit"><?= $creneauEdit ? "Enregistrer les modifications" : "Ajouter" ?></button>
          <?php if ($creneauEdit) : ?>
            <a href="creneaux.php">Annuler</a>
          <?php endif; ?>
        </p>
      </form>
    </section>

    <!-- ============ LISTE DES CRENEAUX ============ -->
    <section>
      <h2>Liste des créneaux</h2>
      <table>
        <caption>Créneaux existants</caption>
        <thead>
          <tr>
            <th scope="col">Date</th>
            <th scope="col">Horaire</th>
            <th scope="col">Salle</th>
            <th scope="col">Inscrits / Jauge</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($c = mysqli_fetch_assoc($creneaux)) : ?>
            <?php
              $dateFr = date('d/m/Y', strtotime($c['date_creneau']));
              $hDebut = substr($c['heure_debut'], 0, 5);
              $hFin   = substr($c['heure_fin'], 0, 5);
            ?>
            <tr>
              <td><?= htmlspecialchars($dateFr) ?></td>
              <td><?= htmlspecialchars("$hDebut à $hFin") ?></td>
              <td><?= htmlspecialchars($c['nom_salle']) ?></td>
              <td><?= (int) $c['nb_inscrits'] ?> / <?= (int) $c['jauge'] ?></td>
              <td>
                <a href="creneaux.php?action=edit&amp;id=<?= (int) $c['id_creneau'] ?>">Modifier</a>
                <form action="creneaux.php" method="post" style="display:inline"
                      onsubmit="return confirm('Supprimer ce créneau ?');">
                  <input type="hidden" name="action" value="supprimer">
                  <input type="hidden" name="id_creneau" value="<?= (int) $c['id_creneau'] ?>">
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
