<?php
// =====================================================================
//  mon-espace.php  -  Espace participant (sans mot de passe).
//  Le visiteur saisit son email pour retrouver ses inscriptions,
//  puis peut CHANGER de creneau ou ANNULER une inscription.
//
//  L'email "connecte" est garde en session le temps de la visite.
//  IMPORTANT : tout le traitement se fait AVANT le HTML (header.php).
// =====================================================================

session_start();
require_once 'connexion.php';

$message = "";

// ---------------------------------------------------------------------
//  TRAITEMENT DES ACTIONS (POST)
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $action = $_POST['action'] ?? '';

    // --- CONNEXION : on retrouve le participant par son email ---
    if ($action === 'connexion') {
        $email = trim($_POST['email'] ?? '');
        $req = mysqli_prepare($CONNEXION, "SELECT id_participant, prenom FROM participant WHERE email = ?");
        mysqli_stmt_bind_param($req, "s", $email);
        mysqli_stmt_execute($req);
        $p = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        if ($p) {
            $_SESSION['participant_id']     = $p['id_participant'];
            $_SESSION['participant_prenom'] = $p['prenom'];
        } else {
            $message = "Aucune inscription trouvée pour cet e-mail.";
        }
    }

    // --- DECONNEXION ---
    if ($action === 'deconnexion') {
        unset($_SESSION['participant_id'], $_SESSION['participant_prenom']);
    }

    // --- ANNULER une inscription (seulement si elle appartient au participant) ---
    if ($action === 'annuler' && isset($_SESSION['participant_id'])) {
        $idInscription = (int) ($_POST['id_inscription'] ?? 0);
        $req = mysqli_prepare($CONNEXION,
            "DELETE FROM inscription WHERE id_inscription = ? AND id_participant = ?");
        mysqli_stmt_bind_param($req, "ii", $idInscription, $_SESSION['participant_id']);
        mysqli_stmt_execute($req);
        $message = "Inscription annulée.";
    }

    // --- MODIFIER : changer le creneau d'une inscription ---
    if ($action === 'modifier' && isset($_SESSION['participant_id'])) {
        $idInscription = (int) ($_POST['id_inscription'] ?? 0);
        $nouveauCreneau = (int) ($_POST['nouveau_creneau'] ?? 0);
        $idParticipant = $_SESSION['participant_id'];

        // 1) Le nouveau creneau est-il complet ?
        $req = mysqli_prepare($CONNEXION,
            "SELECT c.jauge, COUNT(i.id_inscription) AS nb
             FROM creneau c
             LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
             WHERE c.id_creneau = ?
             GROUP BY c.id_creneau, c.jauge");
        mysqli_stmt_bind_param($req, "i", $nouveauCreneau);
        mysqli_stmt_execute($req);
        $info = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        // 2) Est-il deja inscrit a ce nouveau creneau ?
        $req = mysqli_prepare($CONNEXION,
            "SELECT id_inscription FROM inscription WHERE id_creneau = ? AND id_participant = ?");
        mysqli_stmt_bind_param($req, "ii", $nouveauCreneau, $idParticipant);
        mysqli_stmt_execute($req);
        $dejaInscrit = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        if (!$info) {
            $message = "Créneau invalide.";
        } elseif (($info['jauge'] - $info['nb']) <= 0) {
            $message = "Ce créneau est complet.";
        } elseif ($dejaInscrit) {
            $message = "Vous êtes déjà inscrit à ce créneau.";
        } else {
            // 3) On change le creneau de l'inscription (en verifiant le proprietaire).
            $req = mysqli_prepare($CONNEXION,
                "UPDATE inscription SET id_creneau = ? WHERE id_inscription = ? AND id_participant = ?");
            mysqli_stmt_bind_param($req, "iii", $nouveauCreneau, $idInscription, $idParticipant);
            mysqli_stmt_execute($req);
            $message = "Créneau modifié.";
        }
    }
}

// ---------------------------------------------------------------------
//  DONNEES POUR L'AFFICHAGE (si le participant est "connecte")
// ---------------------------------------------------------------------
$mesInscriptions = null;
$creneauxDispo = [];

if (isset($_SESSION['participant_id'])) {

    // Les inscriptions du participant (avec details du creneau et de la salle).
    $sql = "SELECT i.id_inscription, c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle
            FROM inscription i
            JOIN creneau c ON c.id_creneau = i.id_creneau
            JOIN salle s   ON s.id_salle   = c.id_salle
            WHERE i.id_participant = ?
            ORDER BY c.date_creneau, c.heure_debut";
    $req = mysqli_prepare($CONNEXION, $sql);
    mysqli_stmt_bind_param($req, "i", $_SESSION['participant_id']);
    mysqli_stmt_execute($req);
    $mesInscriptions = mysqli_stmt_get_result($req);

    // Les creneaux ayant encore de la place (pour le menu "changer de creneau").
    $sql = "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle,
                   (c.jauge - COUNT(i.id_inscription)) AS places
            FROM creneau c
            JOIN salle s ON s.id_salle = c.id_salle
            LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
            GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle, c.jauge
            HAVING places > 0
            ORDER BY c.date_creneau, c.heure_debut, s.nom_salle";
    $res = mysqli_query($CONNEXION, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $creneauxDispo[] = $row;
    }
}

require_once 'header.php';
?>

  <main>
    <h1>Mon inscription</h1>

    <?php if ($message !== '') : ?>
      <p role="status"><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <?php if (!isset($_SESSION['participant_id'])) : ?>
      <!-- ============ PAS CONNECTE : formulaire email ============ -->
      <p>Saisissez l'adresse e-mail utilisée lors de votre inscription pour la retrouver.</p>
      <form action="mon-espace.php" method="post">
        <input type="hidden" name="action" value="connexion">
        <p>
          <label for="email">Adresse e-mail</label><br>
          <input type="email" id="email" name="email" required>
        </p>
        <p><button type="submit">Retrouver mon inscription</button></p>
      </form>

    <?php else : ?>
      <!-- ============ CONNECTE : liste des inscriptions ============ -->
      <p>Bonjour <strong><?= htmlspecialchars($_SESSION['participant_prenom']) ?></strong>.
        <form action="mon-espace.php" method="post" style="display:inline">
          <input type="hidden" name="action" value="deconnexion">
          <button type="submit">Me déconnecter</button>
        </form>
      </p>

      <?php if (mysqli_num_rows($mesInscriptions) === 0) : ?>
        <p>Vous n'avez aucune inscription. <a href="inscription.php">S'inscrire</a></p>
      <?php else : ?>
        <?php while ($ins = mysqli_fetch_assoc($mesInscriptions)) : ?>
          <?php
            $dateFr = date('d/m/Y', strtotime($ins['date_creneau']));
            $hDebut = substr($ins['heure_debut'], 0, 5);
            $hFin   = substr($ins['heure_fin'], 0, 5);
          ?>
          <section>
            <h2><?= htmlspecialchars("$dateFr — $hDebut à $hFin — {$ins['nom_salle']}") ?></h2>

            <!-- Changer de creneau -->
            <form action="mon-espace.php" method="post">
              <input type="hidden" name="action" value="modifier">
              <input type="hidden" name="id_inscription" value="<?= (int) $ins['id_inscription'] ?>">
              <p>
                <label for="creneau<?= (int) $ins['id_inscription'] ?>">Changer pour un autre créneau :</label><br>
                <select id="creneau<?= (int) $ins['id_inscription'] ?>" name="nouveau_creneau" required>
                  <option value="">-- Choisir un créneau --</option>
                  <?php foreach ($creneauxDispo as $cd) : ?>
                    <?php
                      $d = date('d/m/Y', strtotime($cd['date_creneau']));
                      $hd = substr($cd['heure_debut'], 0, 5);
                      $hf = substr($cd['heure_fin'], 0, 5);
                      $lib = "$d — $hd à $hf — {$cd['nom_salle']} ({$cd['places']} place(s))";
                    ?>
                    <option value="<?= (int) $cd['id_creneau'] ?>"><?= htmlspecialchars($lib) ?></option>
                  <?php endforeach; ?>
                </select>
              </p>
              <p><button type="submit">Modifier</button></p>
            </form>

            <!-- Annuler -->
            <form action="mon-espace.php" method="post"
                  onsubmit="return confirm('Annuler cette inscription ?');">
              <input type="hidden" name="action" value="annuler">
              <input type="hidden" name="id_inscription" value="<?= (int) $ins['id_inscription'] ?>">
              <p><button type="submit">Annuler cette inscription</button></p>
            </form>
          </section>
        <?php endwhile; ?>
      <?php endif; ?>
    <?php endif; ?>
  </main>

<?php require_once 'footer.php'; ?>
