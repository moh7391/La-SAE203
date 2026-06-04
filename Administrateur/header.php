<?php
function admin_e($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

function admin_active($page, $active_page) {
    if ($page == $active_page) {
        return ' class="active"';
    }

    return '';
}

if (!isset($page_title)) {
    $page_title = 'E-LLUSION admin';
}

if (!isset($active_page)) {
    $active_page = '';
}

if (!isset($body_class)) {
    $body_class = '';
}

if (!isset($admin_show_nav)) {
    $admin_show_nav = true;
}

$body_class = trim('admin-body ' . $body_class);
$logo_link = 'login.php';

if ($admin_show_nav) {
    $logo_link = 'accueil.php';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo admin_e($page_title); ?></title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="<?php echo admin_e($body_class); ?>">
  <header class="admin-header">
    <a class="admin-logo" href="<?php echo $logo_link; ?>" aria-label="Accueil admin E-LLUSION">E-LLUSION<span></span></a>

    <?php if ($admin_show_nav) { ?>
      <nav class="admin-nav" aria-label="Navigation administrateur">
        <a href="accueil.php"<?php echo admin_active('dashboard', $active_page); ?>>Tableau de bord</a>
        <a href="creneaux.php"<?php echo admin_active('creneaux', $active_page); ?>>Cr&eacute;neaux</a>
        <a href="inscrits.php"<?php echo admin_active('inscrits', $active_page); ?>>Inscriptions</a>
        <a href="salles.php"<?php echo admin_active('salles', $active_page); ?>>Salles</a>
        <a class="logout" href="deconnexion.php">D&eacute;connexion</a>
      </nav>
    <?php } ?>
  </header>
