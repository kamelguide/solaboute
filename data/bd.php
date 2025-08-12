<?php
// /salonbeauty-main/data/bd.php
declare(strict_types=1);

$serveur        = '127.0.0.1';   // évite 'localhost' pour forcer TCP
$utilisateur    = 'root';
$motDePasse     = '';
$baseDeDonnees  = 'salonbeauty';
$dbPort         = 3307;          // <- TON PORT

try {
    $dsn = "mysql:host={$serveur};port={$dbPort};dbname={$baseDeDonnees};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch assoc
        PDO::ATTR_EMULATE_PREPARES   => false,                  // vrais prepared
    ];
    $connexion = new PDO($dsn, $utilisateur, $motDePasse, $options);
} catch (PDOException $e) {
    // Ne pas envoyer d'HTML ici, sinon risque de "headers already sent"
    die('La connexion à la base de données a échoué : ' . $e->getMessage());
}
