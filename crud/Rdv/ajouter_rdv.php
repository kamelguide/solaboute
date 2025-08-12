<?php
declare(strict_types=1);

/**
 * APP_ROOT = chemin disque du dossier racine du projet (…/salonbeauty-main)
 * __DIR__ = …/salonbeauty-main/crud/Rdv
 */
define('APP_ROOT', dirname(__DIR__, 2)); // remonte de 2: Rdv -> crud -> racine

// Connexion PDO
require_once APP_ROOT . '/data/bd.php'; // attend …/salonbeauty-main/data/bd.php

// URL base pour les redirections (ex: /salonbeauty-main)
$BASE_URL = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

$nom       = trim($_POST['nom']    ?? '');
$tel       = trim($_POST['tel']    ?? '');
$email     = trim($_POST['email']  ?? '');
$centre    = trim($_POST['centre'] ?? '');
$date_rdv  = $_POST['date']  ?? '';
$heure_rdv = $_POST['heure'] ?? '';
$notes     = trim($_POST['notes']  ?? '');
$services  = $_POST['services'] ?? []; // tableau d'IDs

$serviceIds = array_values(array_unique(array_map('intval', (array)$services)));

if ($nom==='' || $tel==='' || $centre==='' || $date_rdv==='' || $heure_rdv==='' || empty($serviceIds)) {
  $msg = urlencode("Merci de remplir tous les champs obligatoires et de sélectionner au moins un service.");
header("Location: /failed.php?msg={$msg}");
  exit;
}

// Construire début/fin du RDV (durée = 2h)
$debut = DateTime::createFromFormat('Y-m-d H:i', $date_rdv.' '.$heure_rdv);
if (!$debut) {
  $msg = urlencode("Date ou heure invalide.");
header("Location: /failed.php?msg={$msg}");
  exit;
}
$fin = (clone $debut)->modify('+2 hours');
$newStart = $debut->format('Y-m-d H:i:s');
$newEnd   = $fin->format('Y-m-d H:i:s');

// (Optionnel) refuser au-delà des horaires, ex: 10:00–20:00
$open  = (clone $debut)->setTime(10,0,0);
$close = (clone $debut)->setTime(20,0,0);
if ($debut < $open || $fin > $close) {
  $msg = urlencode("Les rendez-vous doivent être entre 10:00 et 20:00 (durée 2h).");
header("Location: /failed.php?msg={$msg}");
  exit;
}

try {
  // 0) Vérifier chevauchement sur le même centre (statuts 1/2 actifs)
  $sqlOverlap = "
    SELECT COUNT(*)
    FROM rdv
    WHERE centre = ?
      AND date_rdv = ?
      AND statut_id IN (1,2)
      AND NOT (
        DATE_ADD(CONCAT(date_rdv,' ',heure_rdv), INTERVAL 2 HOUR) <= ?
        OR CONCAT(date_rdv,' ',heure_rdv) >= ?
      )
  ";
  $stOverlap = $connexion->prepare($sqlOverlap);
  $stOverlap->execute([$centre, $date_rdv, $newStart, $newEnd]);
  if ((int)$stOverlap->fetchColumn() > 0) {
    $msg = urlencode("Le créneau choisi est déjà réservé (chaque rendez-vous dure 2h). Merci de choisir un autre horaire.");
header("Location: /failed.php?msg={$msg}");
    exit;
  }

  $connexion->beginTransaction();

  // 1) Vérifier que les services existent
  $in = implode(',', array_fill(0, count($serviceIds), '?'));
  $check = $connexion->prepare("SELECT id FROM services WHERE id IN ($in)");
  $check->execute($serviceIds);
  $idsOk = array_map('intval', $check->fetchAll(PDO::FETCH_COLUMN));
  $manquants = array_diff($serviceIds, $idsOk);
  if (!empty($manquants)) {
    throw new RuntimeException("Services inexistants: ".implode(',', $manquants));
  }

  // 2) Insérer le RDV (sans colonne 'services' texte)
  $sqlRdv = "INSERT INTO rdv (nom, tel, email, centre, date_rdv, heure_rdv, notes, statut_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
  $stmt = $connexion->prepare($sqlRdv);
  $stmt->execute([$nom, $tel, $email, $centre, $date_rdv, $heure_rdv, $notes]);
  $rdv_id = (int)$connexion->lastInsertId();

  // 3) Lier les services (many-to-many)
  $link = $connexion->prepare("INSERT INTO rdv_services (rdv_id, service_id) VALUES (?, ?)");
  foreach ($serviceIds as $sid) {
    $link->execute([$rdv_id, $sid]);
  }

  $connexion->commit();
header("Location: /merci.php");
  exit;

} catch (Throwable $e) {
  if ($connexion->inTransaction()) $connexion->rollBack();
  $msg = urlencode("Erreur lors de l'enregistrement : ".$e->getMessage());
header("Location: /failed.php?msg={$msg}");
  exit;
}
