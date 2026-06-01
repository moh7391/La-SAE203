<?php
// =====================================================================
//  inscription.php  -  Formulaire d'inscription a un creneau (public).
//  - Affiche la liste des creneaux ayant encore des places.
//  - Verifie : champs valides, creneau non complet, email pas deja
//    inscrit a CE creneau.
//  - Enregistre le participant + l'inscription, puis redirige vers merci.php
//
//  IMPORTANT : tout le traitement (et l'eventuelle redirection header())
//  se fait AVANT d'afficher le moindre HTML (avant header.php).
// =====================================================================

require_once 'connexion.php';

$erreurs = [];

// Valeurs pour re-remplir le formulaire en cas d'erreur.
$nom = $prenom = $email = $telephone = "";
$creneauChoisi = isset($_GET['id_creneau']) ? (int) $_GET['id_creneau'] : 0;


// ---------------------------------------------------------------------
//  TRAITEMENT DU FORMULAIRE (uniquement en POST)
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // --- 1) Recuperation et nettoyage ---
    $nom           = trim($_POST['nom'] ?? '');
    $prenom        = trim($_POST['prenom'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $telephone     = trim($_POST['telephone'] ?? '');
    $creneauChoisi = (int) ($_POST['id_creneau'] ?? 0);

    // --- 2) Validation des champs ---
    if ($nom === '')    { $erreurs[] = "Le nom est obligatoire."; }
    if ($prenom === '') { $erreurs[] = "Le prénom est obligatoire."; }
    if ($email === '') {
        $erreurs[] = "L'adresse e-mail est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse e-mail n'est pas valide.";
    }
    if ($creneauChoisi <= 0) { $erreurs[] = "Veuillez choisir un créneau."; }

    // --- 3) Le creneau existe-t-il et reste-t-il de la place ? ---
    if (empty($erreurs)) {
        $sql = "SELECT c.jauge, COUNT(i.id_inscription) AS nb_inscrits
                FROM creneau c
                LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
                WHERE c.id_creneau = ?
                GROUP BY c.id_creneau, c.jauge";
        $req = mysqli_prepare($CONNEXION, $sql);
        mysqli_stmt_bind_param($req, "i", $creneauChoisi);
        mysqli_stmt_execute($req);
        $infoCreneau = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        if (!$infoCreneau) {
            $erreurs[] = "Le créneau choisi n'existe pas.";
        } elseif (($infoCreneau['jauge'] - $infoCreneau['nb_inscrits']) <= 0) {
            $erreurs[] = "Désolé, ce créneau est complet.";
        }
    }

    // --- 4) Participant : on le retrouve via son email, sinon on le cree ---
    if (empty($erreurs)) {
        $req = mysqli_prepare($CONNEXION, "SELECT id_participant FROM participant WHERE email = ?");
        mysqli_stmt_bind_param($req, "s", $email);
        mysqli_stmt_execute($req);
        $participant = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        if ($participant) {
            $idParticipant = $participant['id_participant'];

            // 4a) Deja inscrit a CE creneau ? -> refus du doublon.
            $req = mysqli_prepare($CONNEXION,
                "SELECT id_inscription FROM inscription WHERE id_creneau = ? AND id_participant = ?");
            mysqli_stmt_bind_param($req, "ii", $creneauChoisi, $idParticipant);
            mysqli_stmt_execute($req);
            if (mysqli_fetch_assoc(mysqli_stmt_get_result($req))) {
                $erreurs[] = "Cette adresse e-mail est déjà inscrite à ce créneau.";
            }
        } else {
            // Nouveau participant.
            $req = mysqli_prepare($CONNEXION,
                "INSERT INTO participant (nom, prenom, email, telephone) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($req, "ssss", $nom, $prenom, $email, $telephone);
            mysqli_stmt_execute($req);
            $idParticipant = mysqli_insert_id($CONNEXION);
        }
    }

    // --- 5) Enregistrement de l'inscription + redirection ---
    if (empty($erreurs)) {
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


// ---------------------------------------------------------------------
//  CRENEAUX DISPONIBLES (pour la liste deroulante)
// ---------------------------------------------------------------------
$sql = "SELECT c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin,
               s.nom_salle, (c.jauge - COUNT(i.id_inscription)) AS places_restantes
        FROM creneau c
        JOIN salle s ON s.id_salle = c.id_salle
        LEFT JOIN inscription i ON i.id_creneau = c.id_creneau
        GROUP BY c.id_creneau, c.date_creneau, c.heure_debut, c.heure_fin, s.nom_salle, c.jauge
        HAVING places_restantes > 0
        ORDER BY c.date_creneau, c.heure_debut, s.nom_salle";
$creneaux = mysqli_query($CONNEXION, $sql);

// A partir d'ici on affiche le HTML : on inclut l'en-tete commun.
require_once 'header.php';
?>

  <main>
    <h1>Inscription à l'exposition</h1>
    <p><a href="index.php">&larr; Retour à l'accueil</a></p>

    <?php if (!empty($erreurs)) : ?>
      <section aria-label="Erreurs du formulaire">
        <p><strong>Le formulaire contient des erreurs :</strong></p>
        <ul>
          <?php foreach ($erreurs as $erreur) : ?>
            <li><?= htmlspecialchars($erreur) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <form action="inscription.php" method="post">
      <fieldset>
        <legend>Vos informations</legend>

        <p>
          <label for="nom">Nom <span aria-hidden="true">*</span></label><br>
          <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($nom) ?>">
        </p>
        <p>
          <label for="prenom">Prénom <span aria-hidden="true">*</span></label><br>
          <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($prenom) ?>">
        </p>
        <p>
          <label for="email">Adresse e-mail <span aria-hidden="true">*</span></label><br>
          <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">
        </p>
        <p>
          <label for="telephone">Téléphone</label><br>
          <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone) ?>">
        </p>
      </fieldset>

      <fieldset>
        <legend>Créneau souhaité</legend>
        <p>
          <label for="id_creneau">Choisissez un créneau <span aria-hidden="true">*</span></label><br>
          <select id="id_creneau" name="id_creneau" required>
            <option value="">-- Sélectionnez un créneau --</option>
            <?php while ($c = mysqli_fetch_assoc($creneaux)) : ?>
              <?php
                $dateFr = date('d/m/Y', strtotime($c['date_creneau']));
                $hDebut = substr($c['heure_debut'], 0, 5);
                $hFin   = substr($c['heure_fin'], 0, 5);
                $libelle = "$dateFr — $hDebut à $hFin — {$c['nom_salle']} ({$c['places_restantes']} place(s))";
                $selection = ($c['id_creneau'] == $creneauChoisi) ? 'selected' : '';
              ?>
              <option value="<?= $c['id_creneau'] ?>" <?= $selection ?>><?= htmlspecialchars($libelle) ?></option>
            <?php endwhile; ?>
          </select>
        </p>
      </fieldset>

      <p><button type="submit">Je m'inscris</button></p>
      <p><small><span aria-hidden="true">*</span> Champs obligatoires.</small></p>
    </form>
  </main>

<?php require_once 'footer.php'; ?>
