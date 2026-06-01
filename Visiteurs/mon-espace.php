<?php
// =====================================================================
//  mon-espace.php  -  Le visiteur retrouve ses inscriptions avec son
//  email (sans mot de passe), puis peut changer de creneau ou annuler.
//  L'email est garde en session le temps de la visite.
// =====================================================================

session_start();
require_once 'connexion.php';

$message = "";

// ---------- ACTIONS (POST) ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- Se connecter : on cherche le participant par son email ---
    if ($action === 'connexion') {
        $email = trim($_POST['email']);
        $req = mysqli_prepare($CONNEXION, "SELECT id_participant, prenom FROM participant WHERE email = ?");
        mysqli_stmt_bind_param($req, "s", $email);
        mysqli_stmt_execute($req);
        $resultat = mysqli_stmt_get_result($req);
        $participant = mysqli_fetch_assoc($resultat);

        if ($participant) {
            $_SESSION['participant_id']     = $participant['id_participant'];
            $_SESSION['participant_prenom'] = $participant['prenom'];
        } else {
            $message = "Aucune inscription trouvee pour cet e-mail.";
        }
    }

    // --- Se deconnecter ---
    if ($action === 'deconnexion') {
        session_destroy();
        header("Location: mon-espace.php");
        exit;
    }

    // --- Annuler une de ses inscriptions ---
    if ($action === 'annuler' && isset($_SESSION['participant_id'])) {
        $idInscription = (int) $_POST['id_inscription'];
        // "AND id_participant = ?" : on ne peut annuler que SES propres inscriptions.
        $req = mysqli_prepare($CONNEXION,
            "DELETE FROM inscription WHERE id_inscription = ? AND id_participant = ?");
        mysqli_stmt_bind_param($req, "ii", $idInscription, $_SESSION['participant_id']);
        mysqli_stmt_execute($req);
        $message = "Inscription annulee.";
    }

    // --- Changer le creneau d'une inscription ---
    if ($action === 'modifier' && isset($_SESSION['participant_id'])) {
        $idInscription  = (int) $_POST['id_inscription'];
        $nouveauCreneau = (int) $_POST['nouveau_creneau'];
        // On change le creneau (uniquement pour SES inscriptions).
        $req = mysqli_prepare($CONNEXION,
            "UPDATE inscription SET id_creneau = ? WHERE id_inscription = ? AND id_participant = ?");
        mysqli_stmt_bind_param($req, "iii", $nouveauCreneau, $idInscription, $_SESSION['participant_id']);
        mysqli_stmt_execute($req);
        $message = "Creneau modifie.";
    }
}

// ---------- DONNEES A AFFICHER (si connecte) ----------
$mesInscriptions = null;
$creneauxDispo   = null;

if (isset($_SESSION['participant_id'])) {

    // Les inscriptions du participant.
    $req = mysqli_prepare($CONNEXION,
        "SELECT i.id_inscription, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle
         FROM inscription i
         JOIN creneau c ON c.id_creneau = i.id_creneau
         JOIN salle s   ON s.id_salle   = c.id_salle
         WHERE i.id_participant = ?
         ORDER BY c.date_creneau, c.heure_debut");
    mysqli_stmt_bind_param($req, "i", $_SESSION['participant_id']);
    mysqli_stmt_execute($req);
    $mesInscriptions = mysqli_stmt_get_result($req);

    // Les creneaux qui ont encore de la place (pour le menu "changer de creneau").
    $creneauxDispo = mysqli_query($CONNEXION,
        "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle,
                (c.jauge - COUNT(i.id_inscription)) AS places
         FROM creneau c
         JOIN salle s ON s.id_salle = c.id_salle
         LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
         GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle, c.jauge
         HAVING places > 0
         ORDER BY c.date_creneau, c.heure_debut, s.nom_salle");
}

require_once 'header.php';
?>

  <main>
    <h1>Mon inscription</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION['participant_id'])) : ?>
      <!-- Pas connecte : on demande l'email -->
      <p>Saisissez l'adresse e-mail utilisee lors de votre inscription.</p>
      <form action="mon-espace.php" method="post">
        <input type="hidden" name="action" value="connexion">
        <p>
          <label for="email">Adresse e-mail</label><br>
          <input type="email" id="email" name="email" required>
        </p>
        <p><button type="submit">Retrouver mon inscription</button></p>
      </form>

    <?php else : ?>
      <!-- Connecte : on affiche ses inscriptions -->
      <p>Bonjour <strong><?= htmlspecialchars($_SESSION['participant_prenom']) ?></strong>.</p>
      <form action="mon-espace.php" method="post">
        <input type="hidden" name="action" value="deconnexion">
        <button type="submit">Me deconnecter</button>
      </form>

      <?php if (mysqli_num_rows($mesInscriptions) == 0) : ?>
        <p>Vous n'avez aucune inscription. <a href="inscription.php">S'inscrire</a></p>
      <?php else : ?>
        <?php while ($ins = mysqli_fetch_assoc($mesInscriptions)) : ?>
          <?php
            $date = date('d/m/Y', strtotime($ins['date_creneau']));
            $debut = substr($ins['heure_debut'], 0, 5);
            $fin   = substr($ins['heure_fin'], 0, 5);
          ?>
          <section>
            <h2><?= htmlspecialchars("$date - $debut a $fin - {$ins['nom_salle']}") ?></h2>

            <!-- Changer de creneau -->
            <form action="mon-espace.php" method="post">
              <input type="hidden" name="action" value="modifier">
              <input type="hidden" name="id_inscription" value="<?= $ins['id_inscription'] ?>">
              <p>
                <label for="c<?= $ins['id_inscription'] ?>">Changer de creneau :</label><br>
                <select id="c<?= $ins['id_inscription'] ?>" name="nouveau_creneau" required>
                  <option value="">-- Choisir --</option>
                  <?php
                    // On remet le pointeur au debut de la liste des creneaux pour chaque inscription.
                    mysqli_data_seek($creneauxDispo, 0);
                    while ($cd = mysqli_fetch_assoc($creneauxDispo)) :
                      $d = date('d/m/Y', strtotime($cd['date_creneau']));
                      $hd = substr($cd['heure_debut'], 0, 5);
                      $hf = substr($cd['heure_fin'], 0, 5);
                      $texte = "$d - $hd a $hf - {$cd['nom_salle']} ({$cd['places']} places)";
                  ?>
                    <option value="<?= $cd['id_creneau'] ?>"><?= htmlspecialchars($texte) ?></option>
                  <?php endwhile; ?>
                </select>
              </p>
              <p><button type="submit">Modifier</button></p>
            </form>

            <!-- Annuler -->
            <form action="mon-espace.php" method="post"
                  onsubmit="return confirm('Annuler cette inscription ?');">
              <input type="hidden" name="action" value="annuler">
              <input type="hidden" name="id_inscription" value="<?= $ins['id_inscription'] ?>">
              <p><button type="submit">Annuler</button></p>
            </form>
          </section>
        <?php endwhile; ?>
      <?php endif; ?>
    <?php endif; ?>
  </main>

<?php require_once 'footer.php'; ?>
