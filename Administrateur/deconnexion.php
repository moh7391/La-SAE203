<?php
// =====================================================================
//  deconnexion.php  -  Ferme la session de l'administrateur.
// =====================================================================

session_start();
session_unset();    // vide les variables de session
session_destroy();  // detruit la session

header('Location: login.php');
exit;
?>
