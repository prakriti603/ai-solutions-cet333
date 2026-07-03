<?php
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $dbname = 'ai_solutions';
    $db_user = 'root';
    $db_pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Incorrect username or password. Please try again.';
        }
    } catch (PDOException $e) {
        $error = 'Database connection failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AI-Solutions | Admin Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --navy: #0B1B2B; --blue: #1A6FC4; --accent: #00E5C0;
      --light: #F0F6FF; --white: #FFFFFF; --gray: #6B7C93;
    }
    body {
      font-family: 'DM Sans', sans-serif;
      background: linear-gradient(135deg, var(--navy) 0%, #112B47 60%, #0D3460 100%);
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      padding: 2rem;
    }
    .login-card {
      background: var(--white); border-radius: 24px;
      padding: 3rem; width: 100%; max-width: 420px;
      box-shadow: 0 24px 60px rgba(0,0,0,0.3);
    }
    .login-logo {
      text-align: center; margin-bottom: 2rem;
    }
    .login-logo .logo-icon {
      width: 64px; height: 64px; border-radius: 18px;
      background: linear-gradient(135deg, var(--blue), var(--accent));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem; margin: 0 auto 1rem;
    }
    .login-logo h1 {
      font-family: 'Syne', sans-serif; font-size: 1.4rem;
      font-weight: 800; color: var(--navy);
    }
    .login-logo h1 span { color: var(--blue); }
    .login-logo p { color: var(--gray); font-size: 0.85rem; margin-top: 0.3rem; }
    .form-group { margin-bottom: 1.3rem; }
    .form-group label { display: block; font-size: 0.83rem; font-weight: 600; color: var(--navy); margin-bottom: 0.4rem; }
    .form-group input {
      width: 100%; padding: 0.85rem 1rem;
      border: 1.5px solid #D1E3F8; border-radius: 10px;
      font-size: 0.9rem; font-family: inherit; color: var(--navy);
      outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26,111,196,0.1); }
    .error-msg {
      background: rgba(229,62,62,0.08); border: 1px solid rgba(229,62,62,0.3);
      color: #C53030; padding: 0.85rem 1rem; border-radius: 10px;
      font-size: 0.85rem; margin-bottom: 1.3rem;
    }
    .login-btn {
      width: 100%; padding: 0.95rem;
      background: linear-gradient(135deg, var(--blue), #38AEEC);
      color: white; border: none; border-radius: 10px;
      font-size: 1rem; font-weight: 700; font-family: inherit;
      cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 4px 20px rgba(26,111,196,0.3);
    }
    .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(26,111,196,0.4); }
    .back-link { text-align: center; margin-top: 1.5rem; }
    .back-link a { color: var(--gray); font-size: 0.83rem; text-decoration: none; transition: color 0.2s; }
    .back-link a:hover { color: var(--blue); }
    .security-note {
      display: flex; align-items: center; gap: 0.5rem;
      background: var(--light); border-radius: 10px;
      padding: 0.75rem 1rem; margin-bottom: 1.5rem;
      font-size: 0.8rem; color: var(--gray);
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">🔒</div>
      <h1>AI<span>-Solutions</span></h1>
      <p>Admin Portal — Authorised Access Only</p>
    </div>

    <?php if ($error): ?>
      <div class="error-msg">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="security-note">
      🛡️ This area is restricted to authorised personnel only.
    </div>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required autocomplete="off"/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required/>
      </div>
      <button type="submit" class="login-btn">Login to Dashboard →</button>
    </form>

    <div class="back-link">
      <a href="../index.html">← Back to Website</a>
    </div>
  </div>
</body>
</html>
