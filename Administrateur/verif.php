<?php
// =====================================================================
//  verif.php  -  Garde de session pour l'espace administrateur.
//  A inclure TOUT EN HAUT de chaque page admin protegee.
//  Si l'admin n'est pas connecte -> redirection vers login.php
// =====================================================================

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
