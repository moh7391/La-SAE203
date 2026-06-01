<?php
// =====================================================================
//  merci.php  -  Page de confirmation apres une inscription reussie.
//  Recoit l'identifiant de l'inscription via ?id=... et affiche un
//  recapitulatif (qui, quel creneau, quelle salle).
// =====================================================================

require_once 'connexion.php';

$idInscription = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$sql = "SELECT p.nom, p.prenom, p.email,
               c.date_creneau, c.heure_debut, c.heure_fin,
               s.nom_salle
        FROM inscription i
        JOIN participant p ON p.id_participant = i.id_participant
        JOIN creneau c     ON c.id_creneau     = i.id_creneau
        JOIN salle s       ON s.id_salle       = c.id_salle
        WHERE i.id_inscription = ?";
$req = mysqli_prepare($CONNEXION, $sql);
mysqli_stmt_bind_param($req, "i", $idInscription);
mysqli_stmt_execute($req);
$info = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

require_once 'header.php';
?>

  <main>
    <h1>Confirmation d'inscription</h1>

    <?php if (!$info) : ?>
      <p>Inscription introuvable.</p>
      <p><a href="inscription.php">Retour au formulaire d'inscription</a></p>
    <?php else : ?>
      <?php
        $dateFr = date('d/m/Y', strtotime($info['date_creneau']));
        $hDebut = substr($info['heure_debut'], 0, 5);
        $hFin   = substr($info['heure_fin'], 0, 5);
      ?>
      <p>Merci <strong><?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?></strong>,
         votre inscription est bien enregistrée !</p>

      <h2>Récapitulatif</h2>
      <ul>
        <li><strong>Date :</strong> <?= htmlspecialchars($dateFr) ?></li>
        <li><strong>Horaire :</strong> <?= htmlspecialchars("$hDebut à $hFin") ?></li>
        <li><strong>Salle :</strong> <?= htmlspecialchars($info['nom_salle']) ?></li>
        <li><strong>E-mail :</strong> <?= htmlspecialchars($info['email']) ?></li>
      </ul>

      <p><a href="index.php">Retour à l'accueil</a></p>
    <?php endif; ?>
  </main>

<?php require_once 'footer.php'; ?>
