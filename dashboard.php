<?php
include("config.php");
include("includes/auth.php");
$page_title = "User Dashboard";

require_login();

if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: /pacita1_reservation/admin/dashboard.php");
    exit();
}

include("includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">User Dashboard</h1>
      <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>.</p>
    </div>
  </div>

  <div class="row">
    <a class="btn btn-primary" href="/pacita1_reservation/user/reserve.php">🏸 Reserve a Court</a>
    <a class="btn" href="/pacita1_reservation/user/mybookings.php">📅 My Bookings</a>
  </div>

  <div style="margin-top:14px;" class="row">
    <a class="btn" href="/pacita1_reservation/user/events.php">🏆 Events & Tournaments</a>
    <a class="btn" href="/pacita1_reservation/user/announcements.php">📢 Announcements</a>
  </div>
</div>

<?php include("includes/footer.php"); ?>