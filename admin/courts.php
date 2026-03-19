<?php
include("../config.php");
include("../includes/auth.php");

$page_title="Manage Courts";
require_admin();

/* ADD COURT */
if(isset($_POST['add'])){
  $name   = mysqli_real_escape_string($conn, $_POST['name']);
  $sport  = mysqli_real_escape_string($conn, $_POST['sport']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);

  mysqli_query($conn,"
    INSERT INTO courts (name,sport,status)
    VALUES ('$name','$sport','$status')
  ");
}

/* TOGGLE STATUS (POST) */
if(isset($_POST['toggle_status'])){
  $id = (int)$_POST['court_id'];
  $new_status = ($_POST['new_status'] === 'maintenance') ? 'maintenance' : 'available';

  mysqli_query($conn, "UPDATE courts SET status='$new_status' WHERE id=$id");
}

/* DELETE COURT (POST) */
if(isset($_POST['delete_court'])){
  $id = (int)$_POST['court_id'];
  mysqli_query($conn, "DELETE FROM courts WHERE id=$id");
}

$courts = mysqli_query($conn,"SELECT * FROM courts ORDER BY id DESC");

include("../includes/header.php");
?>

<style>
/* Table alignment + spacing */
.table th, .table td{
  padding: 14px 16px;
  vertical-align: middle;
}
.table th{
  text-align: left;
  font-weight: 700;
  letter-spacing: .3px;
}
.table th.center, .table td.center{
  text-align: center;
}
.table td.actions{
  text-align: right;
  white-space: nowrap;
}
.action-wrap{
  display: inline-flex;
  align-items: center;
  gap: 14px;
}

/* Toggle switch */
.switch{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  user-select: none;
}
.switch-label{
  font-size: 13px;
  opacity: 0.9;
}

.toggle{
  position: relative;
  width: 54px;
  height: 30px;
  display: inline-block;
}
.toggle input{
  display:none;
}
.slider{
  position:absolute;
  cursor:pointer;
  inset:0;
  border-radius: 999px;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.18);
  transition: .2s;
}
.slider:before{
  content:"";
  position:absolute;
  height: 22px;
  width: 22px;
  left: 4px;
  top: 50%;
  transform: translateY(-50%);
  border-radius: 50%;
  background: rgba(255,255,255,0.85);
  transition: .2s;
}
.toggle input:checked + .slider{
  background: rgba(72, 239, 128, 0.20); /* active-ish */
}
.toggle input:checked + .slider:before{
  transform: translate(24px, -50%);
}

/* Make delete button consistent size (optional) */
.btn.btn-danger{
  padding: 8px 14px;
  border-radius: 12px;
}
</style>

<div class="card">

  <h1 class="title">Manage Courts</h1>

  <form method="POST">
    <input class="input" name="name" placeholder="Court Name" required>

    <select class="input" name="sport">
      <option>Tennis</option>
      <option>Badminton</option>
      <option>Basketball</option>
      <option>Volleyball</option>
    </select>

    <select class="input" name="status">
      <option value="available">Available</option>
      <option value="maintenance">Maintenance</option>
    </select>

    <button class="btn btn-primary" name="add">Add Court</button>
  </form>

  <hr>

  <table class="table">
    <tr>
      <th>Court</th>
      <th class="center">Sport</th>
      <th class="center">Status</th>
      <th class="center">Toggle</th>
      <th class="actions">Action</th>
    </tr>

    <?php while($row=mysqli_fetch_assoc($courts)): 
      $isMaintenance = ($row['status'] === 'maintenance');
      // Toggle ON = available (active), OFF = maintenance
      $isActive = ($row['status'] === 'available');
    ?>
      <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>

        <td class="center"><?php echo htmlspecialchars($row['sport']); ?></td>

        <td class="center">
          <?php if($isActive): ?>
            <span style="opacity:.95;">available</span>
          <?php else: ?>
            <span style="opacity:.95;">maintenance</span>
          <?php endif; ?>
        </td>

        <td class="center">
          <!-- Toggle form -->
          <form method="POST" style="display:inline;">
            <input type="hidden" name="court_id" value="<?php echo (int)$row['id']; ?>">
            <input type="hidden" name="new_status" value="<?php echo $isActive ? 'maintenance' : 'available'; ?>">
            <span class="switch">
              <span class="switch-label"><?php echo $isActive ? "Active" : "Maint"; ?></span>

              <label class="toggle" title="Toggle status">
                <input type="checkbox"
                       <?php echo $isActive ? "checked" : ""; ?>
                       onchange="this.form.submit()">
                <span class="slider"></span>
              </label>
            </span>

            <input type="hidden" name="toggle_status" value="1">
          </form>
        </td>

        <td class="actions">
          <span class="action-wrap">
            <!-- Delete -->
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('Delete this court? This cannot be undone.');">
              <input type="hidden" name="court_id" value="<?php echo (int)$row['id']; ?>">
              <button type="submit" name="delete_court" class="btn btn-danger">Delete</button>
            </form>
          </span>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

</div>

<?php include("../includes/footer.php"); ?>