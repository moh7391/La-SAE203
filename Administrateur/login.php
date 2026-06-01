<?php
// =====================================================================
//  login.php  -  Connexion de l'administrateur.
//  Verifie le login + le mot de passe (password_verify sur le hash)
//  puis ouvre une session. En cas de succes -> accueil.php
// =====================================================================

session_start();
require_once 'connexion.php';

// Si l'admin est deja connecte, on l'envoie directement au tableau de bord.
if (isset($_SESSION['admin_id'])) {
    header('Location: accueil.php');
    exit;
}

$erreur = "";

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if ($login === '' || $mdp === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // On cherche l'administrateur par son login (requete preparee).
        $req = mysqli_prepare($CONNEXION,
            "SELECT id_admin, mot_de_passe, nom FROM administrateur WHERE login = ?");
        mysqli_stmt_bind_param($req, "s", $login);
        mysqli_stmt_execute($req);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($req));

        // password_verify compare le mot de passe saisi au hash stocke.
        if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
            // Connexion reussie : on memorise l'admin en session.
            $_SESSION['admin_id']  = $admin['id_admin'];
            $_SESSION['admin_nom'] = $admin['nom'];
            header('Location: accueil.php');
            exit;
        } else {
            // Message volontairement vague (ne pas dire si c'est le login ou le mdp).
            $erreur = "Identifiants incorrects.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion administrateur</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <main>
    <h1>Espace administrateur</h1>

    <?php if ($erreur !== '') : ?>
      <p role="alert"><strong><?= htmlspecialchars($erreur) ?></strong></p>
    <?php endif; ?>

    <form action="login.php" method="post">
      <p>
        <label for="login">Identifiant</label><br>
        <input type="text" id="login" name="login" required>
      </p>
      <p>
        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
      </p>
      <p><button type="submit">Se connecter</button></p>
    </form>
  </main>
</body>
</html>
