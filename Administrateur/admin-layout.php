<?php
function admin_e($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

function admin_active($page, $activePage) {
    return $page === $activePage ? ' class="active"' : '';
}

function admin_page_start($titre, $activePage = '') {
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo admin_e($titre); ?> - E-LLUSION admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
  <header class="admin-header">
    <a class="admin-logo" href="accueil.php">E-LLUSION</a>
    <nav class="admin-nav" aria-label="Navigation administrateur">
      <a href="accueil.php"<?php echo admin_active('dashboard', $activePage); ?>>Tableau de bord</a>
      <a href="creneaux.php"<?php echo admin_active('creneaux', $activePage); ?>>Créneaux</a>
      <a href="inscrits.php"<?php echo admin_active('inscrits', $activePage); ?>>Inscriptions</a>
      <a href="salles.php"<?php echo admin_active('salles', $activePage); ?>>Salles</a>
      <a class="logout" href="deconnexion.php">Déconnexion</a>
    </nav>
  </header>
<?php
}

function admin_footer() {
?>
  <footer class="admin-footer">
    <p>E-LLUSION - Interface administrateur - Exposition 2026</p>
    <small>Tous droits réservés</small>
  </footer>
</body>
</html>
<?php
}
?>
