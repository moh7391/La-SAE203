<?php
// Tableau de bord de l'administrateur.

require_once 'verif.php'; // protege la page
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecte : <strong><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></strong>
       - <a href="deconnexion.php">Se deconnecter</a></p>
  </header>

  <main>
    <h1>Tableau de bord</h1>
    <ul>
      <li><a href="creneaux.php">Gerer les creneaux</a></li>
      <li><a href="salles.php">Gerer les salles</a></li>
      <li><a href="inscrits.php">Voir les inscrits</a></li>
    </ul>
  </main>
</body>
</html>
