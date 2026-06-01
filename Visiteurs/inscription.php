<?php
// Formulaire d'inscription a un creneau.

require_once 'connexion.php';

$erreur = "";

// On regarde si un creneau est deja choisi (depuis la page d'accueil).
$creneauChoisi = 0;
if (isset($_GET['id_creneau'])) {
    $creneauChoisi = (int) $_GET['id_creneau'];
}

// Quand le visiteur envoie le formulaire :
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // On recupere les donnees. Textes -> mysqli_real_escape_string. Nombre -> (int).
    $nom = mysqli_real_escape_string($CONNEXION, $_POST['nom']);
    $prenom = mysqli_real_escape_string($CONNEXION, $_POST['prenom']);
    $email = mysqli_real_escape_string($CONNEXION, $_POST['email']);
    $telephone = mysqli_real_escape_string($CONNEXION, $_POST['telephone']);
    $creneauChoisi = (int) $_POST['id_creneau'];

    // Verification : tous les champs importants doivent etre remplis.
    if ($nom == "" || $prenom == "" || $email == "" || $creneauChoisi == 0) {
        $erreur = "Merci de remplir tous les champs.";
    } else {

        // 1) Est-ce que le creneau est complet ?
        $res = mysqli_query($CONNEXION,
            "SELECT jauge FROM creneau WHERE id_creneau = $creneauChoisi");
        $creneau = mysqli_fetch_assoc($res);

        $res = mysqli_query($CONNEXION,
            "SELECT COUNT(*) AS nb FROM inscription WHERE id_creneau = $creneauChoisi");
        $compte = mysqli_fetch_assoc($res);

        if ($compte['nb'] >= $creneau['jauge']) {
            $erreur = "Desole, ce creneau est complet.";
        } else {

            // 2) Est-ce que ce participant existe deja (meme email) ?
            $res = mysqli_query($CONNEXION,
                "SELECT id_participant FROM participant WHERE email = '$email'");
            $participant = mysqli_fetch_assoc($res);

            if ($participant) {
                $idParticipant = $participant['id_participant'];
            } else {
                // Nouveau participant : on l'ajoute.
                mysqli_query($CONNEXION,
                    "INSERT INTO participant (nom, prenom, email, telephone)
                     VALUES ('$nom', '$prenom', '$email', '$telephone')");
                $idParticipant = mysqli_insert_id($CONNEXION);
            }

            // 3) Est-ce qu'il est deja inscrit a ce creneau ?
            $res = mysqli_query($CONNEXION,
                "SELECT id_inscription FROM inscription
                 WHERE id_creneau = $creneauChoisi AND id_participant = $idParticipant");
            $deja = mysqli_fetch_assoc($res);

            if ($deja) {
                $erreur = "Cette adresse e-mail est deja inscrite a ce creneau.";
            } else {
                // 4) On enregistre l'inscription puis on va sur la page de confirmation.
                $date = date('Y-m-d');
                mysqli_query($CONNEXION,
                    "INSERT INTO inscription (id_creneau, id_participant, date_inscription)
                     VALUES ($creneauChoisi, $idParticipant, '$date')");
                $idInscription = mysqli_insert_id($CONNEXION);

                header("Location: merci.php?id=$idInscription");
                exit;
            }
        }
    }
}

// Liste des creneaux qui ont encore de la place (pour le menu deroulant).
$creneaux = mysqli_query($CONNEXION,
    "SELECT creneau.*, salle.nom_salle
     FROM creneau
     JOIN salle ON salle.id_salle = creneau.id_salle
     ORDER BY date_creneau, heure_debut");

require_once 'header.php';
?>

  <main>
    <h1>Inscription a l'exposition</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>

    <?php if ($erreur != "") { ?>
      <p><strong><?php echo htmlspecialchars($erreur); ?></strong></p>
    <?php } ?>

    <form action="inscription.php" method="post">
      <p>
        <label for="nom">Nom</label><br>
        <input type="text" id="nom" name="nom" required>
      </p>
      <p>
        <label for="prenom">Prenom</label><br>
        <input type="text" id="prenom" name="prenom" required>
      </p>
      <p>
        <label for="email">Adresse e-mail</label><br>
        <input type="email" id="email" name="email" required>
      </p>
      <p>
        <label for="telephone">Telephone</label><br>
        <input type="tel" id="telephone" name="telephone">
      </p>
      <p>
        <label for="id_creneau">Creneau</label><br>
        <select id="id_creneau" name="id_creneau" required>
          <option value="">-- Choisir un creneau --</option>
          <?php while ($c = mysqli_fetch_assoc($creneaux)) {
              $date = date('d/m/Y', strtotime($c['date_creneau']));
              $debut = substr($c['heure_debut'], 0, 5);
              $fin = substr($c['heure_fin'], 0, 5);
              $texte = "$date - $debut a $fin - " . $c['nom_salle'];
          ?>
            <option value="<?php echo $c['id_creneau']; ?>"><?php echo htmlspecialchars($texte); ?></option>
          <?php } ?>
        </select>
      </p>
      <p><button type="submit">Je m'inscris</button></p>
    </form>
  </main>

<?php require_once 'footer.php'; ?>
