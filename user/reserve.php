<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Reserve Court";
require_login();

$user_id = $_SESSION['user_id'];

$courts = mysqli_query($conn,"SELECT * FROM courts WHERE status='available'");

/* RESERVE COURT */
if(isset($_POST['reserve'])){

    $court_id = $_POST['court_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Combine date + time into DATETIME strings
    $startDT = $booking_date . " " . $start_time . ":00";
    $endDT   = $booking_date . " " . $end_time . ":00";

    /* 30 minute rule */
    $duration = (strtotime($end_time) - strtotime($start_time)) / 60;

    if($duration <= 0){
        $error = "End time must be after start time.";
    } 
    else if($duration % 30 != 0){
        $error="Reservations must be in 30-minute increments.";
    }

    /* Fair use rule (max 2 bookings per day) */
    if(!isset($error)){
        $limit = mysqli_query($conn,"
            SELECT COUNT(*) as total
            FROM bookings
            WHERE user_id='$user_id'
            AND booking_date='$booking_date'
            AND status!='cancelled'
        ");

        $row = mysqli_fetch_assoc($limit);

        if($row['total'] >= 2){
            $error="You can only make 2 bookings per day.";
        }
    }

    /* Maintenance check */
    if(!isset($error)){
        $maintenance = mysqli_query($conn,"
            SELECT id
            FROM courts
            WHERE id='$court_id'
            AND maintenance_start IS NOT NULL
            AND maintenance_end IS NOT NULL
            AND (
                (maintenance_start <= '$startDT' AND maintenance_end > '$startDT')
                OR
                (maintenance_start < '$endDT' AND maintenance_end >= '$endDT')
                OR
                ('$startDT' <= maintenance_start AND '$endDT' >= maintenance_end)
            )
            LIMIT 1
        ");

        if(mysqli_num_rows($maintenance) > 0){
            $error = "This court is under maintenance during the selected time.";
        }
    }

    /* Event conflict check */
if(!isset($error)){
    $eventCheck = mysqli_query($conn,"
        SELECT id FROM events
        WHERE court_id='$court_id'
        AND event_date='$booking_date'
        AND (start_time < '$end_time' AND end_time > '$start_time')
        LIMIT 1
    ");

    if(mysqli_num_rows($eventCheck) > 0){
        $error = "This court is reserved for an event during the selected time.";
    }
}

    /* Conflict detection */
    if(!isset($error)){
        $conflict = mysqli_query($conn,"
            SELECT id FROM bookings
            WHERE court_id='$court_id'
            AND booking_date='$booking_date'
            AND status!='cancelled'
            AND (start_time < '$end_time' AND end_time > '$start_time')
            LIMIT 1
        ");

        if(mysqli_num_rows($conflict)>0){
            $error="This time slot is already booked.";
        }
    }

    /* Insert booking */
    if(!isset($error)){

        mysqli_query($conn,"
            INSERT INTO bookings (user_id,court_id,booking_date,start_time,end_time,status)
            VALUES ('$user_id','$court_id','$booking_date','$start_time','$end_time','approved')
        ");

        $success="Reservation successful!";
    }
}

include("../includes/header.php");
?>

<div class="card">

<h1 class="title">Reserve Court</h1>

<?php if(isset($error)): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if(isset($success)): ?>
<div class="alert alert-ok"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<a href="/pacita1_reservation/availability.php" class="btn btn-secondary">
View Court Availability
</a>

<form class="form" method="POST">

<label>Court</label>
<select class="input" name="court_id" required>

<?php while($row=mysqli_fetch_assoc($courts)): ?>
<option value="<?php echo (int)$row['id'] ?>">
<?php echo htmlspecialchars($row['name']) ?>
</option>
<?php endwhile ?>

</select>

<label>Date</label>
<input class="input" type="date" name="booking_date" required>

<label>Start Time</label>
<input class="input" type="time" name="start_time" required>

<label>End Time</label>
<input class="input" type="time" name="end_time" required>

<button class="btn btn-primary" type="submit" name="reserve">
Reserve
</button>

</form>

</div>

<?php include("../includes/footer.php"); ?>