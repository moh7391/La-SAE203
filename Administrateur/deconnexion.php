<?php
// Deconnexion de l'administrateur.

session_start();
session_destroy();
header("Location: login.php");
exit;
?>
