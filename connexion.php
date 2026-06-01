<?php
// =====================================================================
//  Connexion a la base de donnees avec PDO
//  Ce fichier est inclus (require) par toutes les pages qui ont
//  besoin de la base. Il fournit la variable $pdo.
// =====================================================================

// --- Parametres de connexion (XAMPP en local) ---
$hote = "127.0.0.1";   // serveur MySQL
$port = "3306";        // port MySQL par defaut
$base = "203";         // nom de la base de donnees
$user = "root";        // utilisateur MySQL
$mdp  = "";            // mot de passe (vide par defaut sous XAMPP)

// --- Chaine de connexion (DSN) + charset UTF-8 ---
$dsn = "mysql:host=$hote;port=$port;dbname=$base;charset=utf8mb4";

// --- Options PDO ---
$options = [
    // En cas d'erreur SQL, lancer une exception (plus facile a debuguer)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Recuperer les resultats sous forme de tableau associatif
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Utiliser les vraies requetes preparees (securite anti-injection)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- Tentative de connexion ---
try {
    $pdo = new PDO($dsn, $user, $mdp, $options);
} catch (PDOException $e) {
    // En cas d'echec, on arrete tout avec un message clair
    die("Erreur de connexion a la base de donnees : " . $e->getMessage());
}
?>
