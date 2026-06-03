<?php
if (!isset($page_title)) {
    $page_title = 'E-LLUSION';
}
if (!isset($active_page)) {
    $active_page = '';
}
function nav_active($nom, $active_page) {
    return $nom === $active_page ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="css.css?v=<?php echo filemtime(__DIR__ . '/css.css'); ?>">
</head>
<body>
<header class="client-header">
  <a href="index.php" class="client-logo" aria-label="Accueil E-LLUSION">E-LLUSION<span></span></a>

  <nav class="client-nav" aria-label="Navigation principale">
    <a href="index.php"<?php echo nav_active('accueil', $active_page); ?>>Accueil</a>
    <a href="salles.php"<?php echo nav_active('salles', $active_page); ?>>Les Salles</a>
    <a href="inscription.php"<?php echo nav_active('inscription', $active_page); ?>>S'inscrire</a>
    <a href="mon-espace.php"<?php echo nav_active('reservation', $active_page); ?>>Ma réservation</a>
    <a href="contact.php"<?php echo nav_active('contact', $active_page); ?>>Contact</a>
  </nav>
</header>
