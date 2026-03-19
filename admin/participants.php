<?php
include("../config.php");
include("../includes/auth.php");

require_admin();
$page_title = "Event Participants";

$event_id = isset($_GET['event']) ? intval($_GET['event']) : 0;

/* Remove participant */
if (isset($_GET['remove'])) {
    $reg_id = intval($_GET['remove']);
    mysqli_query($conn, "DELETE FROM event_registrations WHERE id=$reg_id");
    header("Location: /pacita1_reservation/admin/participants.php?event=$event_id");
    exit();
}

/* Event info */
$eventRes = mysqli_query($conn, "SELECT * FROM events WHERE id=$event_id LIMIT 1");
$event = mysqli_fetch_assoc($eventRes);

/* Participants list */
$participants = mysqli_query($conn, "
  SELECT r.id AS reg_id, u.fullname, u.email, r.registered_at
  FROM event_registrations r
  JOIN users u ON r.user_id = u.id
  WHERE r.event_id = $event_id
  ORDER BY r.registered_at DESC
");

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Event Participants</h1>
      <p class="subtitle">
        <?php echo $event ? htmlspecialchars($event['title']) : "Event not found"; ?>
      </p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Joined</th>
      <th>Action</th>
    </tr>

    <?php if ($participants && mysqli_num_rows($participants) > 0): ?>
      <?php while($p=mysqli_fetch_assoc($participants)): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['fullname']); ?></td>
          <td><?php echo htmlspecialchars($p['email']); ?></td>
          <td><?php echo htmlspecialchars($p['registered_at']); ?></td>
          <td style="white-space:nowrap;">
            <a class="btn btn-danger"
               href="?event=<?php echo $event_id; ?>&remove=<?php echo (int)$p['reg_id']; ?>"
               onclick="return confirm('Remove this participant from the event?');">
              Remove
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4">No participants yet.</td></tr>
    <?php endif; ?>
  </table>

  <div style="margin-top:12px;">
    <a class="btn" href="/pacita1_reservation/admin/events.php">Back to Events</a>
  </div>
</div>

<?php include("../includes/footer.php"); ?>