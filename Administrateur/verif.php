<?php
// A inclure en haut de chaque page admin.
// Si l'admin n'est pas connecte, on le renvoie vers la page de connexion.

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
