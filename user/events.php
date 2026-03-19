<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Events";
require_login();

$user_id = $_SESSION['user_id'];

/* JOIN EVENT (only runs when button clicked) */
if (isset($_GET['join'])) {
    $event_id = intval($_GET['join']);

    // Get the event (to check max participants)
    $eventRes = mysqli_query($conn, "SELECT * FROM events WHERE id = $event_id LIMIT 1");
    $event = mysqli_fetch_assoc($eventRes);

    if (!$event) {
        $error = "Event not found.";
    } else {

        // Count current participants
        $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = $event_id");
        $countRow = mysqli_fetch_assoc($countRes);
        $current = (int)$countRow['total'];

        $max = isset($event['max_participants']) ? (int)$event['max_participants'] : 999999;

        // Check if already joined
        $alreadyRes = mysqli_query($conn, "SELECT id FROM event_registrations WHERE event_id=$event_id AND user_id=$user_id LIMIT 1");

        if ($alreadyRes && mysqli_num_rows($alreadyRes) > 0) {
            $error = "You already joined this event.";
        } elseif ($current >= $max) {
            $error = "This event is already full.";
        } else {
            mysqli_query($conn, "INSERT INTO event_registrations (event_id, user_id) VALUES ($event_id, $user_id)");
            $success = "You joined the event!";
        }
    }
}

/* LIST EVENTS */
$events = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Events & Tournaments</h1>
      <p class="subtitle">Join upcoming barangay sports events.</p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-ok"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <table class="table">
    <tr>
      <th>Title</th>
      <th>Description</th>
      <th>Date</th>
      <th>Slots</th>
      <th>Action</th>
    </tr>

    <?php if ($events && mysqli_num_rows($events) > 0): ?>
      <?php while($e = mysqli_fetch_assoc($events)): ?>

        <?php
          $eid = (int)$e['id'];

          // count participants
          $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = $eid");
          $countRow = mysqli_fetch_assoc($countRes);
          $current = (int)$countRow['total'];

          $max = isset($e['max_participants']) ? (int)$e['max_participants'] : 999999;

          // check if user already joined
          $alreadyRes = mysqli_query($conn, "SELECT id FROM event_registrations WHERE event_id=$eid AND user_id=$user_id LIMIT 1");
          $already = ($alreadyRes && mysqli_num_rows($alreadyRes) > 0);

          $isFull = ($current >= $max);
        ?>

        <tr>
          <td><?php echo htmlspecialchars($e['title']); ?></td>
          <td><?php echo htmlspecialchars($e['description'] ?? ""); ?></td>
          <td><?php echo htmlspecialchars($e['event_date']); ?></td>
          <td><?php echo $current . " / " . $max; ?></td>
          <td>
            <?php if ($already): ?>
              <span class="badge approved">Joined</span>
            <?php elseif ($isFull): ?>
              <span class="badge cancelled">Full</span>
            <?php else: ?>
              <a class="btn btn-primary" href="?join=<?php echo $eid; ?>">Join</a>
            <?php endif; ?>
          </td>
        </tr>

      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="5">No events available.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>