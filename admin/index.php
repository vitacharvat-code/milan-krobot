<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: panel.php');
    exit;
}

// Rate limiting & CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$failedAttempts = $_SESSION['login_attempts'] ?? 0;
$lastAttemptTime = $_SESSION['last_attempt_time'] ?? 0;

// Lock after 5 failed attempts for 5 minutes
if ($failedAttempts >= 5) {
    $timeSinceLastAttempt = time() - $lastAttemptTime;
    if ($timeSinceLastAttempt < 300) { // 5 minutes
        $error = 'Příliš mnoho pokusů. Zkuste později.';
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // CSRF token validation
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        exit('Chyba bezpečnosti.');
    }

    $password = $_POST['password'] ?? '';
    $hash = '5d284932eeb7ab619aac20a46dacf80e51a69c79eb624ae358778abf371b0a5a';

    if (hash('sha256', $password) === $hash) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_attempts'] = 0;
        header('Location: panel.php');
        exit;
    } else {
        $_SESSION['login_attempts'] = ($failedAttempts ?? 0) + 1;
        $_SESSION['last_attempt_time'] = time();
        $error = 'Nesprávné heslo.';
        sleep(1); // Delay brute force attacks
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Milan Krobot – Správa webu</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #0f0f0f;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-wrap {
      width: 100%;
      max-width: 400px;
      padding: 24px;
    }
    .login-card {
      background: #1a1a1a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 40px 36px;
    }
    .login-logo {
      font-family: Georgia, serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: #f0ece4;
      margin-bottom: 6px;
    }
    .login-logo span { color: #c8a228; }
    .login-sub {
      font-size: 0.8rem;
      color: #666;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-bottom: 32px;
    }
    label {
      display: block;
      font-size: 0.78rem;
      color: #888;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 8px;
    }
    input[type=password] {
      width: 100%;
      background: #111;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 10px;
      padding: 14px 16px;
      font-size: 1rem;
      color: #f0ece4;
      outline: none;
      transition: border-color 0.2s;
    }
    input[type=password]:focus { border-color: #c8a228; }
    .error {
      background: rgba(200,50,50,0.15);
      border: 1px solid rgba(200,50,50,0.3);
      color: #f08080;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.85rem;
      margin-top: 12px;
    }
    button {
      width: 100%;
      margin-top: 20px;
      background: #c8a228;
      color: #0f0f0f;
      border: none;
      border-radius: 10px;
      padding: 14px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    button:hover { background: #ddb84a; }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-logo">Milan <span>Krobot</span></div>
      <div class="login-sub">Správa webu</div>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="password">Heslo</label>
        <input type="password" id="password" name="password" autofocus required>
        <?php if ($error): ?>
          <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <button type="submit">Přihlásit se</button>
      </form>
    </div>
  </div>
</body>
</html>
