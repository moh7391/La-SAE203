<?php
// Connexion de l'administrateur.

session_start();
require_once 'connexion.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: accueil.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $motDePasse = $_POST['mot_de_passe'];

    $loginSql = mysqli_real_escape_string($CONNEXION, $login);

    $sql = "SELECT *
            FROM administrateur
            WHERE login = '$loginSql'";

    $resultat = mysqli_query($CONNEXION, $sql);
    $admin = mysqli_fetch_assoc($resultat);

    if ($admin && password_verify($motDePasse, $admin['mot_de_passe'])) {
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_nom'] = $admin['nom'];

        header('Location: accueil.php');
        exit;
    }

    $erreur = 'Identifiants incorrects.';
}

$page_title = 'Connexion administrateur - E-LLUSION';
$body_class = 'login-page';
$admin_show_nav = false;
include 'header.php';
?>

  <main class="login-shell">
    <section class="admin-card login-card">
      <h1 class="admin-title">Connexion<br>administrateur</h1>

      <?php if ($erreur != '') { ?>
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

      <p class="login-note">Acc&egrave;s r&eacute;serv&eacute; aux administrateurs</p>
      <span class="login-dot" aria-hidden="true"></span>
    </section>
  </main>

<?php include 'footer.php'; ?>
