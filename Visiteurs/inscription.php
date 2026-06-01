<?php
// =====================================================================
//  inscription.php  -  Formulaire d'inscription a un creneau.
//
//  Deroulement :
//   1. Le visiteur remplit le formulaire (nom, prenom, email, tel, creneau).
//   2. On verifie les champs, puis que le creneau n'est pas complet.
//   3. On enregistre le participant (s'il est nouveau) puis l'inscription.
//   4. On redirige vers la page de remerciement (merci.php).
//
//  Regle de securite : chaque requete qui utilise une donnee du visiteur
//  est une "requete preparee" (prepare + bind_param) -> anti-injection.
// =====================================================================

require_once 'connexion.php';

$erreurs = [];                 // liste des messages d'erreur a afficher
$nom = $prenom = $email = $telephone = "";   // valeurs du formulaire
$creneauChoisi = (int) ($_GET['id_creneau'] ?? 0);  // creneau pre-selectionne


// ---------- TRAITEMENT DU FORMULAIRE (quand on clique sur "Je m'inscris") ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // 1) On recupere les donnees du formulaire.
    $nom           = trim($_POST['nom'] ?? '');
    $prenom        = trim($_POST['prenom'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $telephone     = trim($_POST['telephone'] ?? '');
    $creneauChoisi = (int) ($_POST['id_creneau'] ?? 0);

    // 2) On verifie que tout est rempli correctement.
    if ($nom === '')                              { $erreurs[] = "Le nom est obligatoire."; }
    if ($prenom === '')                           { $erreurs[] = "Le prenom est obligatoire."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $erreurs[] = "L'adresse e-mail n'est pas valide."; }
    if ($creneauChoisi <= 0)                      { $erreurs[] = "Veuillez choisir un creneau."; }

    // 3) On verifie qu'il reste de la place dans le creneau choisi.
    if (!$erreurs) {
        $req = mysqli_prepare($CONNEXION,
            "SELECT c.jauge, COUNT(i.id_inscription) AS nb_inscrits
             FROM creneau c
             LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
             WHERE c.id_creneau = ?
             GROUP BY c.id_creneau, c.jauge");
        mysqli_stmt_bind_param($req, "i", $creneauChoisi);
        mysqli_stmt_execute($req);
        $resultat = mysqli_stmt_get_result($req);
        $creneau  = mysqli_fetch_assoc($resultat);

        if (!$creneau) {
            $erreurs[] = "Le creneau choisi n'existe pas.";
        } elseif ($creneau['jauge'] - $creneau['nb_inscrits'] <= 0) {
            $erreurs[] = "Desole, ce creneau est complet.";
        }
    }

    // 4) On retrouve le participant grace a son email (l'email est unique).
    if (!$erreurs) {
        $req = mysqli_prepare($CONNEXION, "SELECT id_participant FROM participant WHERE email = ?");
        mysqli_stmt_bind_param($req, "s", $email);
        mysqli_stmt_execute($req);
        $resultat = mysqli_stmt_get_result($req);
        $participant = mysqli_fetch_assoc($resultat);

        if ($participant) {
            // Il existe deja : on garde son identifiant.
            $idParticipant = $participant['id_participant'];

            // Est-il deja inscrit a CE creneau ? Si oui, on refuse le doublon.
            $req = mysqli_prepare($CONNEXION,
                "SELECT id_inscription FROM inscription WHERE id_creneau = ? AND id_participant = ?");
            mysqli_stmt_bind_param($req, "ii", $creneauChoisi, $idParticipant);
            mysqli_stmt_execute($req);
            $resultat = mysqli_stmt_get_result($req);
            if (mysqli_fetch_assoc($resultat)) {
                $erreurs[] = "Cette adresse e-mail est deja inscrite a ce creneau.";
            }
        } else {
            // Nouveau participant : on l'ajoute dans la table participant.
            $req = mysqli_prepare($CONNEXION,
                "INSERT INTO participant (nom, prenom, email, telephone) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($req, "ssss", $nom, $prenom, $email, $telephone);
            mysqli_stmt_execute($req);
            $idParticipant = mysqli_insert_id($CONNEXION);
        }
    }

    // 5) Tout est bon : on enregistre l'inscription et on redirige.
    if (!$erreurs) {
        $aujourdhui = date('Y-m-d');
        $req = mysqli_prepare($CONNEXION,
            "INSERT INTO inscription (id_creneau, id_participant, date_inscription) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($req, "iis", $creneauChoisi, $idParticipant, $aujourdhui);
        mysqli_stmt_execute($req);
        $idInscription = mysqli_insert_id($CONNEXION);

        header("Location: merci.php?id=" . $idInscription);
        exit;
    }
}


// ---------- LISTE DES CRENEAUX QUI ONT ENCORE DE LA PLACE ----------
// Requete simple (aucune donnee du visiteur) -> mysqli_query suffit.
$creneaux = mysqli_query($CONNEXION,
    "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle,
            (c.jauge - COUNT(i.id_inscription)) AS places
     FROM creneau c
     JOIN salle s ON s.id_salle = c.id_salle
     LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
     GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle, c.jauge
     HAVING places > 0
     ORDER BY c.date_creneau, c.heure_debut, s.nom_salle");

require_once 'header.php';
?>

  <main>
    <h1>Inscription a l'exposition</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>

    <?php if ($erreurs) : ?>
      <section aria-label="Erreurs du formulaire">
        <p><strong>Merci de corriger :</strong></p>
        <ul>
          <?php foreach ($erreurs as $erreur) : ?>
            <li><?= htmlspecialchars($erreur) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <form action="inscription.php" method="post">
      <p>
        <label for="nom">Nom</label><br>
        <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($nom) ?>">
      </p>
      <p>
        <label for="prenom">Prenom</label><br>
        <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($prenom) ?>">
      </p>
      <p>
        <label for="email">Adresse e-mail</label><br>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">
      </p>
      <p>
        <label for="telephone">Telephone</label><br>
        <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone) ?>">
      </p>
      <p>
        <label for="id_creneau">Creneau</label><br>
        <select id="id_creneau" name="id_creneau" required>
          <option value="">-- Choisir un creneau --</option>
          <?php while ($c = mysqli_fetch_assoc($creneaux)) : ?>
            <?php
              $date = date('d/m/Y', strtotime($c['date_creneau']));
              $debut = substr($c['heure_debut'], 0, 5);
              $fin   = substr($c['heure_fin'], 0, 5);
              $texte = "$date - $debut a $fin - {$c['nom_salle']} ({$c['places']} places)";
            ?>
            <option value="<?= $c['id_creneau'] ?>"><?= htmlspecialchars($texte) ?></option>
          <?php endwhile; ?>
        </select>
      </p>
      <p><button type="submit">Je m'inscris</button></p>
    </form>
  </main>

<?php require_once 'footer.php'; ?>
