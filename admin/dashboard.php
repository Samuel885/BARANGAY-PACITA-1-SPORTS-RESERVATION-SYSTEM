<?php
include("../config.php");
include("../includes/auth.php");

require_admin();
$page_title = "Admin Dashboard";

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Admin Dashboard</h1>
      <p class="subtitle">Welcome, Admin <?php echo htmlspecialchars($_SESSION['fullname']); ?>.</p>
    </div>
  </div>

  <div class="row">
    <a class="btn btn-primary" href="/pacita1_reservation/admin/courts.php">🏟️ Manage Courts</a>
    <a class="btn" href="/pacita1_reservation/admin/bookings.php">✅ Manage Bookings</a>
  </div>

  <div style="margin-top:14px;" class="row">
    <a class="btn" href="/pacita1_reservation/admin/events.php">🏆 Manage Events</a>
    <a class="btn" href="/pacita1_reservation/admin/users.php">👤 Manage Users</a>
  </div>

  <div style="margin-top:14px;">
    <a class="btn" href="/pacita1_reservation/admin/announcements.php">📢 Manage Announcements</a>
  </div>
</div>

<?php include("../includes/footer.php"); ?>