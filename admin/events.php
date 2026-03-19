<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Manage Events";
require_admin();

/* DELETE EVENT (and registrations + linked booking) */
if (isset($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];

    // 1) delete event registrations
    mysqli_query($conn, "DELETE FROM event_registrations WHERE event_id = $event_id");

    // 2) delete the linked blocking booking
    mysqli_query($conn, "DELETE FROM bookings WHERE event_id = $event_id");

    // 3) delete the event itself
    mysqli_query($conn, "DELETE FROM events WHERE id = $event_id");

    // 4) redirect back to same page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* CREATE EVENT */
if (isset($_POST['add'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $court_id = (int)$_POST['court_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $max_participants = (int)$_POST['max_participants'];

    // basic validation
    if (empty($title) || empty($event_date) || empty($court_id) || empty($start_time) || empty($end_time)) {
        $error = "Please fill in all required fields.";
    } elseif ($start_time >= $end_time) {
        $error = "End time must be later than start time.";
    } else {

        // conflict with existing bookings (includes event-block bookings too)
        $check = mysqli_query($conn, "
            SELECT id FROM bookings
            WHERE court_id = $court_id
              AND booking_date = '$event_date'
              AND status != 'cancelled'
              AND (
                    start_time < '$end_time'
                AND end_time > '$start_time'
              )
            LIMIT 1
        ");

        if ($check && mysqli_num_rows($check) > 0) {
            $error = "Event conflicts with an existing booking or blocked schedule.";
        } else {

            // 1) insert the event first
            $stmt = mysqli_prepare($conn, "
                INSERT INTO events (
                    title,
                    description,
                    event_date,
                    court_id,
                    start_time,
                    end_time,
                    max_participants
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "sssissi",
                $title,
                $description,
                $event_date,
                $court_id,
                $start_time,
                $end_time,
                $max_participants
            );

            if (mysqli_stmt_execute($stmt)) {

                // 2) now get the correct event id
                $event_id = mysqli_insert_id($conn);

                // 3) create one linked blocking booking
                $admin_id = (int)$_SESSION['user_id'];

                $stmt2 = mysqli_prepare($conn, "
                    INSERT INTO bookings (
                        user_id,
                        court_id,
                        booking_date,
                        start_time,
                        end_time,
                        status,
                        event_id
                    )
                    VALUES (?, ?, ?, ?, ?, 'approved', ?)
                ");

                mysqli_stmt_bind_param(
                    $stmt2,
                    "iisssi",
                    $admin_id,
                    $court_id,
                    $event_date,
                    $start_time,
                    $end_time,
                    $event_id
                );

                if (mysqli_stmt_execute($stmt2)) {
                    $success = "Event created successfully.";
                } else {
                    // if booking block fails, remove the event so no broken record remains
                    mysqli_query($conn, "DELETE FROM events WHERE id = $event_id");
                    $error = "Event was created, but blocking the court failed. Event creation was rolled back.";
                }

                mysqli_stmt_close($stmt2);

            } else {
                $error = "Failed to create event.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

/* COURTS DROPDOWN */
$courts = mysqli_query($conn, "SELECT id, name FROM courts ORDER BY name ASC");

/* LIST EVENTS */
$events = mysqli_query($conn, "
    SELECT e.*, c.name AS court_name
    FROM events e
    LEFT JOIN courts c ON e.court_id = c.id
    ORDER BY e.event_date ASC, e.start_time ASC
");

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Manage Events</h1>
      <p class="subtitle">Create events/tournaments and manage participants.</p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-ok"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form class="form" method="POST">
    <div class="field">
      <label>Title</label>
      <input class="input" name="title" required>
    </div>

    <div class="field">
      <label>Description</label>
      <textarea class="input" name="description" rows="3"></textarea>
    </div>

    <div class="row" style="display:flex; gap:12px; flex-wrap:wrap;">
      <div class="field" style="flex:1; min-width:220px;">
        <label>Date</label>
        <input class="input" type="date" name="event_date" required>
      </div>

      <div class="field" style="flex:1; min-width:220px;">
        <label>Court</label>
        <select class="input" name="court_id" required>
          <?php if ($courts && mysqli_num_rows($courts) > 0): ?>
            <?php while($c = mysqli_fetch_assoc($courts)): ?>
              <option value="<?php echo (int)$c['id']; ?>">
                <?php echo htmlspecialchars($c['name']); ?>
              </option>
            <?php endwhile; ?>
          <?php else: ?>
            <option value="">No courts available</option>
          <?php endif; ?>
        </select>
      </div>
    </div>

    <div class="row" style="display:flex; gap:12px; flex-wrap:wrap;">
      <div class="field" style="flex:1; min-width:180px;">
        <label>Start</label>
        <input class="input" type="time" name="start_time" required>
      </div>

      <div class="field" style="flex:1; min-width:180px;">
        <label>End</label>
        <input class="input" type="time" name="end_time" required>
      </div>

      <div class="field" style="flex:1; min-width:180px;">
        <label>Max Participants</label>
        <input class="input" type="number" name="max_participants" value="10" min="1" required>
      </div>
    </div>

    <button class="btn btn-primary" type="submit" name="add">Create Event</button>
  </form>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header">
    <div>
      <h2 class="title" style="font-size:20px;">Existing Events</h2>
      <p class="subtitle">View participant count and delete events if needed.</p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>Event</th>
      <th>Court</th>
      <th>Date</th>
      <th>Time</th>
      <th>Participants</th>
      <th class="actions">Actions</th>
    </tr>

    <?php if ($events && mysqli_num_rows($events) > 0): ?>
      <?php while($e = mysqli_fetch_assoc($events)): ?>
        <?php
          $eid = (int)$e['id'];
          $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = $eid");
          $countRow = mysqli_fetch_assoc($countRes);
          $current = (int)$countRow['total'];
          $max = (int)$e['max_participants'];
        ?>
        <tr>
          <td>
            <strong><?php echo htmlspecialchars($e['title']); ?></strong><br>
            <span style="opacity:.85;"><?php echo htmlspecialchars($e['description']); ?></span>
          </td>

          <td><?php echo htmlspecialchars($e['court_name'] ?? ""); ?></td>
          <td><?php echo htmlspecialchars($e['event_date']); ?></td>
          <td><?php echo htmlspecialchars($e['start_time'] . " - " . $e['end_time']); ?></td>
          <td><?php echo $current . " / " . $max; ?></td>

          <td class="actions" style="white-space:nowrap;">
            <a class="btn" href="/pacita1_reservation/admin/participants.php?event=<?php echo $eid; ?>">
              View Participants
            </a>

            <a class="btn btn-danger"
               href="?delete=<?php echo $eid; ?>"
               onclick="return confirm('Delete this event? This will remove all registrations too.');">
              Delete
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="6">No events created yet.</td>
      </tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>