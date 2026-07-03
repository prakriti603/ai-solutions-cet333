<?php
session_start();

// Protect this page — redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'ai_solutions';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total number of inquiries
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM inquiries");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get today's inquiries
    $todayStmt = $pdo->query("SELECT COUNT(*) as today FROM inquiries WHERE DATE(submitted_at) = CURDATE()");
    $today = $todayStmt->fetch(PDO::FETCH_ASSOC)['today'];

    // Get this week's inquiries
    $weekStmt = $pdo->query("SELECT COUNT(*) as week FROM inquiries WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $week = $weekStmt->fetch(PDO::FETCH_ASSOC)['week'];

    // Get latest 10 inquiries
    $latestStmt = $pdo->query("SELECT * FROM inquiries ORDER BY submitted_at DESC LIMIT 10");
    $latestInquiries = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total = 0; $today = 0; $week = 0; $latestInquiries = [];
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AI-Solutions | Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --navy: #0B1B2B; --blue: #1A6FC4; --sky: #38AEEC;
      --accent: #00E5C0; --light: #F0F6FF; --white: #FFFFFF; --gray: #6B7C93;
    }
    body { font-family: 'DM Sans', sans-serif; background: var(--light); color: var(--navy); }

    /* TOPBAR */
    .topbar {
      background: var(--navy); padding: 0 2rem; height: 64px;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .topbar-logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.2rem; color: var(--white); }
    .topbar-logo span { color: var(--accent); }
    .topbar-right { display: flex; align-items: center; gap: 1.5rem; }
    .admin-badge {
      background: rgba(0,229,192,0.15); border: 1px solid rgba(0,229,192,0.3);
      color: var(--accent); font-size: 0.78rem; font-weight: 600;
      padding: 0.3rem 0.8rem; border-radius: 50px;
    }
    .logout-btn {
      background: rgba(229,62,62,0.15); border: 1px solid rgba(229,62,62,0.3);
      color: #FC8181; font-size: 0.82rem; font-weight: 600;
      padding: 0.4rem 1rem; border-radius: 8px;
      text-decoration: none; transition: background 0.2s;
    }
    .logout-btn:hover { background: rgba(229,62,62,0.25); }

    /* MAIN */
    .main { padding: 2.5rem; max-width: 1200px; margin: 0 auto; }
    .page-title { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 800; margin-bottom: 0.3rem; }
    .page-sub { color: var(--gray); font-size: 0.88rem; margin-bottom: 2rem; }

    /* STAT CARDS */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
    .stat-card {
      background: var(--white); border-radius: 16px; padding: 1.8rem;
      border: 1px solid #E2EBF6; display: flex; align-items: center; gap: 1.2rem;
    }
    .stat-icon {
      width: 56px; height: 56px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
    }
    .stat-icon.blue { background: rgba(26,111,196,0.1); }
    .stat-icon.green { background: rgba(0,229,192,0.1); }
    .stat-icon.orange { background: rgba(255,153,0,0.1); }
    .stat-num { font-family: 'Syne', sans-serif; font-size: 2.2rem; font-weight: 800; color: var(--navy); line-height: 1; }
    .stat-label { color: var(--gray); font-size: 0.83rem; margin-top: 0.3rem; }

    /* TABLE */
    .table-card { background: var(--white); border-radius: 16px; border: 1px solid #E2EBF6; overflow: hidden; }
    .table-header { padding: 1.5rem 1.8rem; border-bottom: 1px solid #E2EBF6; display: flex; align-items: center; justify-content: space-between; }
    .table-header h2 { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; }
    .table-header span { color: var(--gray); font-size: 0.82rem; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--light); padding: 0.85rem 1.2rem; text-align: left; font-size: 0.78rem; font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: 0.5px; }
    td { padding: 0.9rem 1.2rem; font-size: 0.85rem; border-bottom: 1px solid #F0F6FF; color: #3D526B; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--light); }
    .no-data { text-align: center; padding: 3rem; color: var(--gray); font-size: 0.9rem; }

    .back-btn {
      display: inline-flex; align-items: center; gap: 0.5rem;
      color: var(--gray); font-size: 0.83rem; text-decoration: none;
      margin-bottom: 1.5rem; transition: color 0.2s;
    }
    .back-btn:hover { color: var(--blue); }
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-logo">AI<span>-Solutions</span> <span style="color:rgba(255,255,255,0.4); font-weight:400; font-size:0.9rem;">| Admin</span></div>
  <div class="topbar-right">
    <span class="admin-badge">🔒 Logged in as: <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
    <a href="?logout=1" class="logout-btn">Logout →</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <a href="../index.html" class="back-btn">← Back to Website</a>
  <div class="page-title">Admin Dashboard</div>
  <div class="page-sub">Overview of all customer inquiries submitted through the Contact Us form.</div>

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon blue">📩</div>
      <div>
        <div class="stat-num"><?= $total ?></div>
        <div class="stat-label">Total Inquiries</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">📅</div>
      <div>
        <div class="stat-num"><?= $today ?></div>
        <div class="stat-label">Today's Inquiries</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange">📊</div>
      <div>
        <div class="stat-num"><?= $week ?></div>
        <div class="stat-label">This Week's Inquiries</div>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="table-card">
    <div class="table-header">
      <h2>Recent Customer Inquiries</h2>
      <span>Showing latest 10 submissions</span>
    </div>
    <?php if (empty($latestInquiries)): ?>
      <div class="no-data">📭 No inquiries yet. Submissions from the Contact Us form will appear here.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Company</th>
          <th>Country</th>
          <th>Job Title</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latestInquiries as $inquiry): ?>
        <tr>
          <td><?= $inquiry['id'] ?></td>
          <td><?= htmlspecialchars($inquiry['full_name']) ?></td>
          <td><?= htmlspecialchars($inquiry['email']) ?></td>
          <td><?= htmlspecialchars($inquiry['phone']) ?></td>
          <td><?= htmlspecialchars($inquiry['company']) ?></td>
          <td><?= htmlspecialchars($inquiry['country']) ?></td>
          <td><?= htmlspecialchars($inquiry['job_title']) ?></td>
          <td><?= date('d M Y, H:i', strtotime($inquiry['submitted_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
