<?php
// =====================================================================
//  accueil.php  -  Tableau de bord de l'administrateur (protege).
// =====================================================================

require_once 'verif.php'; // protege la page : redirige si non connecte
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord administrateur</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header>
    <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
       — <a href="deconnexion.php">Se déconnecter</a></p>
  </header>

  <main>
    <h1>Tableau de bord</h1>
    <nav aria-label="Gestion">
      <ul>
        <li><a href="creneaux.php">Gérer les créneaux</a></li>
        <li><a href="salles.php">Gérer les salles</a></li>
        <li><a href="inscrits.php">Voir les inscrits</a></li>
      </ul>
    </nav>
  </main>
</body>
</html>
