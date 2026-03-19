<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Pacita Sports"; ?></title>

  <!-- IMPORTANT: make sure this matches your filename -->
  <link rel="stylesheet" href="/pacita1_reservation/assets/styles.css">
  
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="/pacita1_reservation/">
      <span class="brand-icon">🏀</span>
      <span class="brand-text">
        <span class="brand-title">Pacita Sports</span>
        <span class="brand-sub">Reservation System</span>
      </span>
    </a>

    <nav class="nav">
      <?php if (!empty($_SESSION['user_id'])): ?>

        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a class="nav-link" href="/pacita1_reservation/admin/dashboard.php">Admin</a>
          <a class="nav-link" href="/pacita1_reservation/admin/courts.php">Courts</a>
          <a class="nav-link" href="/pacita1_reservation/admin/bookings.php">Bookings</a>
          <a class="nav-link" href="/pacita1_reservation/admin/events.php">Events</a>
          <a class="nav-link" href="/pacita1_reservation/admin/users.php">Users</a>
          <a class="nav-link" href="/pacita1_reservation/availability.php">Availability</a>
          <a class="nav-link" href="/pacita1_reservation/admin/announcements.php">Announcements</a>
        <?php else: ?>
          <a class="nav-link" href="/pacita1_reservation/dashboard.php">Dashboard</a>
          <a class="nav-link" href="/pacita1_reservation/user/reserve.php">Reserve</a>
          <a class="nav-link" href="/pacita1_reservation/availability.php">Availability</a>
          <a class="nav-link" href="/pacita1_reservation/user/events.php">Events</a>
          <a class="nav-link" href="/pacita1_reservation/user/announcements.php">Announcements</a>
          <a class="nav-link" href="/pacita1_reservation/user/mybookings.php">My Bookings</a>
          <a class="nav-link" href="/pacita1_reservation/user/my_events.php">My Events</a>
        <?php endif; ?>

        <a class="nav-link nav-cta" href="/pacita1_reservation/logout.php">Logout</a>

      <?php else: ?>
        <a class="nav-link" href="/pacita1_reservation/login.php">Login</a>
        <a class="nav-link nav-cta" href="/pacita1_reservation/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="wrap">