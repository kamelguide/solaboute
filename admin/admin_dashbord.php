<?php
declare(strict_types=1);
session_start();

/**
 * On calcule le répertoire web courant (ex: /salonbeauty-main/admin)
 * pour construire des URLs fiables sans dépendre du nom du dossier.
 */
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

/**
 * Vérifier si l'admin est connecté
 * On teste la clé posée au login: $_SESSION['admin_id']
 */
if (empty($_SESSION['admin_id'])) {
    // Redirection vers la page de login du même dossier
    header('Location: ' . $baseDir . '/admin_login.php');
    exit;
}

// Sécuriser l'affichage du nom
$adminUsername = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Tableau de bord Admin - Wahiba Beauty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
    <h1>Bienvenue <?= $adminUsername ?> !</h1>

    <nav>
        <ul>
            <!-- Liens construits avec $baseDir pour éviter les 404 si le dossier change -->
            <li><a href="<?= $baseDir ?>/lister_centres.php">Gestion des centres</a></li>
            <li><a href="<?= $baseDir ?>/lister_services.php">Gestion des services</a></li>
            <li><a href="<?= $baseDir ?>/lister_rdv.php">Gestion des rendez-vous</a></li>
            <li><a href="<?= $baseDir ?>/logout.php">Déconnexion</a></li>
        </ul>
    </nav>

    <p>Ici tu peux gérer toutes les données de ton site.</p>
</body>
</html>
