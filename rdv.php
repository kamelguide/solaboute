<?php 
include('includes/header.php'); 
require_once __DIR__ . '/data/bd.php'; // $connexion = PDO (DSN avec port 3307)
?>
<style>
  body { background-color:#f5f0e6; margin:0; padding:0; }
  select[multiple] { height:180px; width:100%; padding:10px; font-size:16px; }
</style>
<?php
// Charger les centres pour le <select>
$centres = $connexion->query("
  SELECT nom 
  FROM centres 
  ORDER BY nom
")->fetchAll(PDO::FETCH_COLUMN);

// Charger TOUS les services (avec nom du centre) pour liste groupée
$allServices = $connexion->query("
  SELECT s.id, s.nom_service, s.prix, c.nom
  FROM services s
  JOIN centres c ON c.id = s.centre_id
  ORDER BY c.nom, s.nom_service
")->fetchAll(PDO::FETCH_ASSOC);

// Si on vient d’un bouton avec ?centre=... ou ?ville=...
$centreSelectionne = $_GET['centre'] ?? $_GET['ville'] ?? '';
?>
<section style="max-width: 650px; margin: 50px auto; padding: 20px; background:#f2ecf1; border-radius: 10px;">
  <h1 style="text-align:center; color:#caa2b6;">Prendre Rendez-vous</h1>
  <p style="text-align:center; margin-bottom: 24px;">
    Choisissez votre centre, un ou plusieurs services, puis votre créneau.
  </p>

  <form action="crud/Rdv/ajouter_rdv.php" method="POST" style="display:flex; flex-direction:column; gap:14px;">
    <label for="nom">Nom complet *</label>
    <input type="text" id="nom" name="nom" placeholder="Votre nom" required>

    <label for="tel">Téléphone *</label>
    <input type="tel" id="tel" name="tel" placeholder="+216 12 345 678" required>

    <label for="email">Email (optionnel)</label>
    <input type="email" id="email" name="email" placeholder="votre.email@exemple.com">

    <label for="centre">Centre *</label>
    <select id="centre" name="centre" required>
      <option value="">Choisissez votre centre</option>
      <?php foreach ($centres as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $centreSelectionne===$c ? 'selected':'' ?>>
          <?= htmlspecialchars($c) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="services">Services (un ou plusieurs) *</label>
    <select name="services[]" id="services" multiple required>
      <?php
      $currentCentre = null;
      foreach ($allServices as $srv) {
          if ($currentCentre !== $srv['nom_du_centre']) {
              if ($currentCentre !== null) echo '</optgroup>';
              $currentCentre = $srv['nom_du_centre'];
              echo '<optgroup label="'.htmlspecialchars($currentCentre).'">';
          }
          $label = $srv['nom_service'];
          if ($srv['prix'] !== null) $label .= ' — ' . rtrim(rtrim((string)$srv['prix'],'0'),'.') . ' DT';
          echo '<option value="'.(int)$srv['id'].'">'.htmlspecialchars($label).'</option>';
      }
      if ($currentCentre !== null) echo '</optgroup>';
      ?>
    </select>

    <label for="date">Date *</label>
    <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">

    <label for="heure">Heure *</label>
    <input type="time" id="heure" name="heure" required min="10:00" max="20:00">

    <label for="notes">Notes (optionnel)</label>
    <textarea id="notes" name="notes" rows="3" placeholder="Précisions particulières..."></textarea>

    <button type="submit" style="background:#ba9bc1; color:#fff; padding:12px; border:none; border-radius:6px; cursor:pointer;">
      Confirmer le rendez-vous
    </button>
  </form>
</section>

<?php include('includes/footer.php'); ?>
