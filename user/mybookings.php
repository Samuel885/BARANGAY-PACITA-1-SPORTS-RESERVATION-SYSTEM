<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "My Bookings";
require_login();

$user_id = $_SESSION['user_id'];

/* Cancel booking */
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id=$booking_id AND user_id=$user_id");
    header("Location: /pacita1_reservation/user/mybookings.php");
    exit();
}

$sql = "
SELECT b.id, b.booking_date, b.start_time, b.end_time, b.status,
       c.name AS court_name
FROM bookings b
JOIN courts c ON b.court_id = c.id
WHERE b.user_id = $user_id
ORDER BY b.booking_date DESC, b.start_time DESC
";
$bookings = mysqli_query($conn, $sql);

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">My Bookings</h1>
      <p class="subtitle">View and cancel your reservations.</p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>Court</th>
      <th>Date</th>
      <th>Start</th>
      <th>End</th>
      <th>Status</th>
      <th>Action</th>
    </tr>

    <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
      <?php while($r = mysqli_fetch_assoc($bookings)): ?>
        <tr>
          <td><?php echo htmlspecialchars($r['court_name']); ?></td>
          <td><?php echo htmlspecialchars($r['booking_date']); ?></td>
          <td><?php echo htmlspecialchars($r['start_time']); ?></td>
          <td><?php echo htmlspecialchars($r['end_time']); ?></td>
          <td><span class="badge <?php echo htmlspecialchars($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
          <td>
            <?php if ($r['status'] !== 'cancelled'): ?>
              <a class="nav-link nav-cta" href="?cancel=<?php echo (int)$r['id']; ?>" onclick="return confirm('Cancel this booking?')">Cancel</a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6">No bookings yet.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>