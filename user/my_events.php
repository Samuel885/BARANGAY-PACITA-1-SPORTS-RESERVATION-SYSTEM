<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "My Events";
require_login();

$user_id = $_SESSION['user_id'];

$sql = "
SELECT e.title, e.event_date, r.registered_at
FROM event_registrations r
JOIN events e ON r.event_id = e.id
WHERE r.user_id = $user_id
ORDER BY e.event_date ASC
";
$rows = mysqli_query($conn, $sql);

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">My Events</h1>
      <p class="subtitle">Events and tournaments you registered for.</p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>Event</th>
      <th>Date</th>
      <th>Registered</th>
    </tr>

    <?php if ($rows && mysqli_num_rows($rows) > 0): ?>
      <?php while($r = mysqli_fetch_assoc($rows)): ?>
        <tr>
          <td><?php echo htmlspecialchars($r['title']); ?></td>
          <td><?php echo htmlspecialchars($r['event_date']); ?></td>
          <td><?php echo htmlspecialchars($r['registered_at']); ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="3">You haven’t joined any events yet.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>