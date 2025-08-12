<?php
// failed.php — à placer à la racine de /salonbeauty-main



// Construire l’URL d’accueil proprement (…/salonbeauty-main/index.php)
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$homeUrl = $baseDir . '/accueil.php';

// Récupérer un message d'erreur éventuel
session_start();
$rawMsg = $_GET['msg'] ?? ($_SESSION['error_msg'] ?? '');
if (isset($_SESSION['error_msg'])) unset($_SESSION['error_msg']); // on consomme

$msg = trim($rawMsg) !== '' ? htmlspecialchars($rawMsg, ENT_QUOTES, 'UTF-8') : "Une erreur est survenue lors de l'enregistrement de votre rendez-vous.";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Oups… Échec – Wahiba Beauty</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --bg: linear-gradient(135deg, #fff1f1 0%, #f8f0f0 100%);
      --card: #ffffff;
      --accent: #e06b6b;       /* rouge doux */
      --accent-dark: #c85b5b;
      --text: #2b2b2b;
      --muted: #6b6b6b;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; }
    body {
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
      background: var(--bg);
      color: var(--text);
      display: grid;
      place-items: center;
      padding: 24px;
    }
    .wrap {
      width: 100%;
      max-width: 720px;
      background: var(--card);
      border-radius: 18px;
      box-shadow: 0 20px 45px rgba(0,0,0,.08);
      padding: 36px 28px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .wrap::before, .wrap::after {
      content: "";
      position: absolute;
      inset: -80px -60px auto auto;
      width: 240px; height: 240px;
      background: radial-gradient(ellipse at center, rgba(224,107,107,.12), transparent 60%);
      transform: rotate(25deg);
      pointer-events: none;
    }
    .wrap::after {
      inset: auto auto -80px -60px;
      transform: rotate(-15deg);
    }
    .icon {
      width: 86px; height: 86px;
      margin: 6px auto 14px;
      display: grid; place-items: center;
      border-radius: 50%;
      background: rgba(224,107,107,.12);
      animation: pop .6s ease-out both;
    }
    @keyframes pop { 0% { transform: scale(.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    h1 { font-size: clamp(22px, 3.4vw, 30px); margin: 6px 0 8px; }
    p.desc { margin: 0 auto 22px; max-width: 560px; color: var(--muted); line-height: 1.55; font-size: 15px; }
    .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
    .btn {
      display: inline-block;
      padding: 12px 18px;
      border-radius: 10px;
      background: var(--accent);
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      transition: transform .12s ease, filter .2s ease;
      box-shadow: 0 10px 22px rgba(224,107,107,.2);
    }
    .btn:hover { transform: translateY(-1px); filter: brightness(.98); }
    .btn:active { transform: translateY(0); filter: brightness(.95); }
    .btn.secondary {
      background: transparent;
      color: var(--accent-dark);
      border: 2px solid rgba(224,107,107,.35);
      box-shadow: none;
    }
    .sub { margin-top: 14px; font-size: 13px; color: var(--muted); }
  </style>
</head>
<body>
  <main class="wrap" role="main" aria-labelledby="fail-title">
    <div class="icon" aria-hidden="true">
      <!-- Cross icon -->
      <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#cc5f5f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="10" opacity=".25"></circle>
        <path d="M15 9l-6 6M9 9l6 6"></path>
      </svg>
    </div>

    <h1 id="fail-title">Oups… échec de l’opération</h1>
    <p class="desc"><?= $msg ?></p>

    <div class="actions">
      <a class="btn" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES) ?>">← Retour à l’accueil</a>
      <a class="btn secondary" href="javascript:history.back()">↺ Réessayer</a>
    </div>

    <div class="sub">Si le problème persiste, contactez-nous par téléphone ou réessayez plus tard.</div>
  </main>
</body>
</html>
<?php

?>
