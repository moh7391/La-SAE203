<?php
// Connexion de l'administrateur.

session_start();
require_once 'connexion.php';

// Si l'admin est deja connecte, on l'envoie au tableau de bord.
if (isset($_SESSION['admin_id'])) {
    header("Location: accueil.php");
    exit;
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $login = mysqli_real_escape_string($CONNEXION, $_POST['login']);
    $mdp = $_POST['mot_de_passe'];

    // On cherche l'admin qui a ce login.
    $res = mysqli_query($CONNEXION,
        "SELECT * FROM administrateur WHERE login = '$login'");
    $admin = mysqli_fetch_assoc($res);

    // password_verify compare le mot de passe tape avec le mot de passe hache.
    if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_nom'] = $admin['nom'];
        header("Location: accueil.php");
        exit;
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion administrateur</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <main>
    <h1>Espace administrateur</h1>

    <?php if ($erreur != "") { ?>
      <p><strong><?php echo htmlspecialchars($erreur); ?></strong></p>
    <?php } ?>

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
