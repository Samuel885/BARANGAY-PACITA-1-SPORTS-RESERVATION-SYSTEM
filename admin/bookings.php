<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Manage Bookings";
require_admin();

/* DELETE BOOKING (POST) */
if (isset($_POST['delete_booking'])) {
  $booking_id = (int)$_POST['booking_id'];

  mysqli_query($conn, "DELETE FROM bookings WHERE id = $booking_id");

  // Redirect back to THIS exact page (correct path always)
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

$sql = "
SELECT 
  b.id,
  b.booking_date,
  b.start_time,
  b.end_time,
  b.status,
  u.fullname,
  c.name AS court_name
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN courts c ON b.court_id = c.id
ORDER BY b.booking_date DESC, b.start_time DESC
";

$bookings = mysqli_query($conn, $sql);

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Manage Bookings</h1>
      <p class="subtitle">View all reservations made by users.</p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>User</th>
      <th>Court</th>
      <th>Date</th>
      <th>Start</th>
      <th>End</th>
      <th>Status</th>
      <th class="actions">Action</th>
    </tr>

    <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
      <?php while($row = mysqli_fetch_assoc($bookings)): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['fullname']); ?></td>
          <td><?php echo htmlspecialchars($row['court_name']); ?></td>
          <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
          <td><?php echo htmlspecialchars($row['start_time']); ?></td>
          <td><?php echo htmlspecialchars($row['end_time']); ?></td>
          <td>
            <span class="badge <?php echo htmlspecialchars($row['status']); ?>">
              <?php echo htmlspecialchars($row['status']); ?>
            </span>
          </td>

          <td class="actions">
            <form method="POST"
                  onsubmit="return confirm('Delete this booking? This cannot be undone.');">
              <input type="hidden" name="booking_id" value="<?php echo (int)$row['id']; ?>">
              <button type="submit" name="delete_booking" class="btn btn-danger">
                Delete
              </button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="7">No bookings found yet.</td>
      </tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>