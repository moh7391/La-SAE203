<?php
// Connexion de l'administrateur.

session_start();
require_once 'connexion.php';
require_once 'admin-layout.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: accueil.php");
    exit;
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $login = mysqli_real_escape_string($CONNEXION, $_POST['login']);
    $mdp = $_POST['mot_de_passe'];

    $res = mysqli_query($CONNEXION, "SELECT * FROM administrateur WHERE login = '$login'");
    $admin = mysqli_fetch_assoc($res);

    if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_nom'] = $admin['nom'];
        header("Location: accueil.php");
        exit;
    }

    $erreur = "Identifiants incorrects.";
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion administrateur - E-LLUSION</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body login-page">
  <header class="admin-header">
    <a class="admin-logo" href="login.php">E-LLUSION</a>
  </header>

  <main class="login-shell">
    <section class="admin-card login-card">
      <h1 class="admin-title">Connexion<br>administrateur</h1>

      <?php if ($erreur != "") { ?>
        <p class="admin-error"><?php echo admin_e($erreur); ?></p>
      <?php } ?>

      <form class="login-form" action="login.php" method="post">
        <div class="field">
          <label for="login">Login</label>
          <input type="text" id="login" name="login" placeholder="Entrez votre login" required>
        </div>

        <div class="field">
          <label for="mot_de_passe">Mot de passe</label>
          <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
        </div>

        <button class="admin-btn" type="submit">Se connecter</button>
      </form>

      <p class="login-note">Accès réservé aux administrateurs</p>
      <span class="login-dot" aria-hidden="true"></span>
    </section>
  </main>

  <?php admin_footer(); ?>
