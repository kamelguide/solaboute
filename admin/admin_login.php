<?php
session_start();

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'salonbeauty';
$dbPort = 3307;

$error = null;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $error = "Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Veuillez saisir le nom d'utilisateur et le mot de passe.";
    } else {
        try {
            // Récupère id + username + password_hash
            $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = "Identifiants invalides.";
            } else {
                $stored = (string)($user['password_hash'] ?? '');
                // supporte hash (bcrypt/argon) ou clair (ancien dump)
                $isHashed = (bool)preg_match('/^\$2[ayb]\$|\$argon2(id|i|d)\$/', $stored);
                $ok = $isHashed ? password_verify($password, $stored)
                                : hash_equals($stored, $password);

                if ($ok) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id']       = (int)$user['id'];
                    $_SESSION['admin_username'] = (string)$user['username'];

                    // Si le mot de passe était en clair, on le remplace par un hash sécurisé
                    if (!$isHashed) {
                        $newHash = password_hash($stored, PASSWORD_BCRYPT);
                        $up = $conn->prepare("UPDATE admins SET password_hash=? WHERE id=?");
                        $up->bind_param('si', $newHash, $user['id']);
                        $up->execute();
                        $up->close();
                    }

                    header('Location: admin_dashbord.php'); // adapte si besoin
                    exit;
                } else {
                    $error = "Identifiants invalides.";
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Erreur lors de la connexion : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Connexion Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body { font-family: Arial, sans-serif; background:#f5f6fa; margin:0; }
    .login-container {
      max-width: 380px; margin: 8vh auto; background:#fff; padding:24px 26px;
      border-radius:14px; box-shadow: 0 12px 28px rgba(0,0,0,.08);
    }
    h1 { margin:0 0 18px; font-size:22px; }
    label { display:block; margin:12px 0 6px; font-weight:600; }
    input[type="text"], input[type="password"] {
      width:100%; padding:10px 12px; border:1px solid #dcdde1; border-radius:10px; font-size:14px;
      outline:none;
    }
    input:focus { border-color:#6c5ce7; box-shadow:0 0 0 3px rgba(108,92,231,.15); }
    .error {
      background:#ffe9e9; color:#b20000; padding:10px 12px; border-radius:10px; margin-bottom:12px; font-size:14px;
      border:1px solid #ffc8c8;
    }
    button {
      margin-top:16px; width:100%; padding:12px; border:none; border-radius:10px;
      background:#6c5ce7; color:#fff; font-size:15px; font-weight:700; cursor:pointer;
    }
    button:hover { filter:brightness(0.95); }
  </style>
</head>
<body>
  <div class="login-container">
    <h1>Connexion Admin</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="username">Nom d'utilisateur</label>
      <input type="text" id="username" name="username" required autofocus>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Se connecter</button>
    </form>
  </div>
</body>
</html>
