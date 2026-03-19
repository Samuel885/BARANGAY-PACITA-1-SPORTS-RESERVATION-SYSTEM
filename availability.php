<?php
include("config.php");
include("includes/auth.php");

require_login();
$page_title = "Court Availability";

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

/* --- helper: generate slots --- */
function timeSlots($start="06:00", $end="22:00", $stepMinutes=30){
  $slots = [];
  $t = strtotime($start);
  $endT = strtotime($end);
  while($t < $endT){
    $slotStart = date("H:i", $t);
    $t2 = strtotime("+$stepMinutes minutes", $t);
    $slotEnd = date("H:i", $t2);
    $slots[] = [$slotStart, $slotEnd];
    $t = $t2;
  }
  return $slots;
}

/* --- COURTS LIST --- */
$courtsRes = mysqli_query($conn, "SELECT id, name, status, maintenance_start, maintenance_end FROM courts ORDER BY name ASC");
$courts = [];
while($c = mysqli_fetch_assoc($courtsRes)) {
  $courts[] = $c;
}

/* Default selections */
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_court = isset($_GET['court_id']) ? (int)$_GET['court_id'] : (count($courts) ? (int)$courts[0]['id'] : 0);

/* --- ADMIN: SET / CLEAR MAINTENANCE --- */
if ($is_admin && isset($_POST['set_maintenance'])) {
    $court_id = (int)$_POST['court_id'];
    $m_start = $_POST['maintenance_start'];
    $m_end = $_POST['maintenance_end'];

    if ($m_start >= $m_end) {
        $error = "Maintenance end must be after start.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE courts SET maintenance_start=?, maintenance_end=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $m_start, $m_end, $court_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: " . $_SERVER['PHP_SELF'] . "?date=".$selected_date."&court_id=".$court_id);
        exit();
    }
}

if ($is_admin && isset($_POST['clear_maintenance'])) {
    $court_id = (int)$_POST['court_id'];
    mysqli_query($conn, "UPDATE courts SET maintenance_start=NULL, maintenance_end=NULL WHERE id=$court_id");
    header("Location: " . $_SERVER['PHP_SELF'] . "?date=".$selected_date."&court_id=".$court_id);
    exit();
}

/* --- FETCH EVENTS --- */
$eventsRes = mysqli_query($conn, "
  SELECT title, start_time, end_time
  FROM events
  WHERE court_id = $selected_court
    AND event_date = '$selected_date'
  ORDER BY start_time ASC
");

/* --- FETCH BOOKINGS (ONLY those NOT overlapping events) --- */
$bookingsRes = mysqli_query($conn, "
  SELECT b.start_time, b.end_time, b.status
  FROM bookings b
  WHERE b.court_id = $selected_court
    AND b.booking_date = '$selected_date'
    AND b.status != 'cancelled'
    AND NOT EXISTS (
      SELECT 1
      FROM events e
      WHERE e.court_id = b.court_id
        AND e.event_date = b.booking_date
        AND (e.start_time < b.end_time AND e.end_time > b.start_time)
    )
  ORDER BY b.start_time ASC
");

/* --- Get selected court maintenance info --- */
$selCourtRes = mysqli_query($conn, "SELECT * FROM courts WHERE id=$selected_court LIMIT 1");
$selCourt = mysqli_fetch_assoc($selCourtRes);

$mStart = $selCourt['maintenance_start'] ?? null;
$mEnd = $selCourt['maintenance_end'] ?? null;

/* Put events + bookings into arrays for easy slot checking */
$events = [];
if ($eventsRes) {
  while($ev = mysqli_fetch_assoc($eventsRes)) $events[] = $ev;
}

$bookings = [];
if ($bookingsRes) {
  while($b = mysqli_fetch_assoc($bookingsRes)) $bookings[] = $b;
}

/* Helper: overlap check for time-only within same date */
function overlaps($aStart, $aEnd, $bStart, $bEnd){
  return ($aStart < $bEnd && $aEnd > $bStart);
}

include("includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Court Availability</h1>
      <p class="subtitle">Select a court and date to view schedule in 30-minute time slots.</p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <!-- FILTERS -->
  <form class="form" method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
    <div class="field" style="min-width:220px;">
      <label>Court</label>
      <select class="input" name="court_id" required>
        <?php foreach($courts as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$c['id'] === $selected_court) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field" style="min-width:180px;">
      <label>Date</label>
      <input class="input" type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
    </div>

    <div class="field">
      <button class="btn btn-primary" type="submit">View</button>
    </div>
  </form>

  <hr style="margin:16px 0; opacity:.2;">

  <!-- MAINTENANCE STATUS -->
  <div style="margin-bottom:14px;">
    <strong>Status:</strong>
    <?php
      if (!empty($mStart) && !empty($mEnd)) {
        echo '<span class="badge cancelled">Maintenance</span> ';
        echo '<span style="opacity:.9;">('.htmlspecialchars($mStart).' to '.htmlspecialchars($mEnd).')</span>';
      } else {
        echo '<span class="badge approved">Available</span>';
      }
    ?>
  </div>

  <!-- ADMIN MAINTENANCE CONTROLS -->
  <?php if ($is_admin): ?>
    <div class="card" style="margin: 12px 0; padding:14px;">
      <h3 style="margin:0 0 10px 0;">Admin: Set Maintenance Window</h3>

      <form method="POST" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
        <input type="hidden" name="court_id" value="<?php echo (int)$selected_court; ?>">

        <div class="field" style="min-width:240px;">
          <label>Maintenance Start</label>
          <input class="input" type="datetime-local" name="maintenance_start" required>
        </div>

        <div class="field" style="min-width:240px;">
          <label>Maintenance End</label>
          <input class="input" type="datetime-local" name="maintenance_end" required>
        </div>

        <div class="field">
          <button class="btn btn-warning" type="submit" name="set_maintenance">Set</button>
        </div>

        <div class="field">
          <button class="btn btn-danger" type="submit" name="clear_maintenance"
            onclick="return confirm('Clear maintenance for this court?');">
            Clear
          </button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- TIMETABLE -->
  <h2 style="margin-top:10px;">Time Slots on <?php echo htmlspecialchars($selected_date); ?></h2>

  <table class="table" style="margin-top:10px;">
    <tr>
      <th class="center">Time</th>
      <th>Availability</th>
      <th>Details</th>
    </tr>

    <?php
      $slots = timeSlots("06:00", "22:00", 30);
      $printedAny = false;

      // maintenance time portion for the selected date (if maintenance spans days, we clip display)
      $maintenanceStartTime = null;
      $maintenanceEndTime = null;

      if (!empty($mStart) && !empty($mEnd)) {
        $msDate = substr($mStart, 0, 10);
        $meDate = substr($mEnd, 0, 10);

        if ($selected_date >= $msDate && $selected_date <= $meDate) {
          // for same-day maintenance, use exact times; for spanning days, show full day blocks
          $maintenanceStartTime = ($selected_date == $msDate) ? substr($mStart, 11, 5) : "00:00";
          $maintenanceEndTime   = ($selected_date == $meDate) ? substr($mEnd, 11, 5) : "23:59";
        }
      }

      foreach($slots as $slot){
        [$sStart, $sEnd] = $slot;

        $type = "Available";
        $badge = "approved";
        $details = "Open";

        // 1) Maintenance highest priority
        if (!empty($maintenanceStartTime) && !empty($maintenanceEndTime) && overlaps($sStart, $sEnd, $maintenanceStartTime, $maintenanceEndTime)) {
          $type = "Maintenance";
          $badge = "cancelled";
          $details = "Court unavailable";
        } else {
          // 2) Events
          foreach($events as $ev){
            if (overlaps($sStart, $sEnd, $ev['start_time'], $ev['end_time'])) {
              $type = "Event";
              $badge = "pending";
              $details = $ev['title'];
              break;
            }
          }

          // 3) Bookings (only if not already event)
          if ($type === "Available") {
            foreach($bookings as $b){
              if (overlaps($sStart, $sEnd, $b['start_time'], $b['end_time'])) {
                $type = "Booked";
                $badge = "approved";
                $details = "Reserved";
                break;
              }
            }
          }
        }

        $printedAny = true;

        echo "<tr>
          <td class='center'>".htmlspecialchars($sStart)." - ".htmlspecialchars($sEnd)."</td>
          <td><span class='badge $badge'>".htmlspecialchars($type)."</span></td>
          <td>".htmlspecialchars($details)."</td>
        </tr>";
      }

      if (!$printedAny) {
        echo "<tr><td colspan='3'>No slots to display.</td></tr>";
      }
    ?>
  </table>

</div>

<?php include("includes/footer.php"); ?>