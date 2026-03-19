<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Manage Announcements";
require_admin();

/* CREATE */
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title === "" || $content === "") {
        $error = "Title and content are required.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO announcements (title, content) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $title, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $success = "Announcement posted!";
    }
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM announcements WHERE id=$id");
    header("Location: /pacita1_reservation/admin/announcements.php");
    exit();
}

/* LIST */
$ann = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Manage Announcements</h1>
      <p class="subtitle">Create announcements and manage existing ones.</p>
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
      <label>Content</label>
      <textarea class="input" name="content" rows="5" required></textarea>
    </div>

    <button class="btn btn-primary" type="submit" name="add">Post Announcement</button>
  </form>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header">
    <div>
      <h2 class="title" style="font-size:20px;">Posted Announcements</h2>
      <p class="subtitle">This is what users will see.</p>
    </div>
  </div>

  <table class="table">
    <tr>
      <th>Title</th>
      <th>Posted</th>
      <th>Action</th>
    </tr>

    <?php if ($ann && mysqli_num_rows($ann) > 0): ?>
      <?php while($a = mysqli_fetch_assoc($ann)): ?>
        <tr>
          <td>
            <strong><?php echo htmlspecialchars($a['title']); ?></strong>
            <div style="opacity:.85; margin-top:6px;">
              <?php echo nl2br(htmlspecialchars($a['content'])); ?>
            </div>
          </td>
          <td style="white-space:nowrap;">
            <?php echo htmlspecialchars($a['created_at']); ?>
          </td>
          <td style="white-space:nowrap;">
            <a class="btn btn-danger"
               href="?delete=<?php echo (int)$a['id']; ?>"
               onclick="return confirm('Delete this announcement?');">
              Delete
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="3">No announcements posted yet.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php include("../includes/footer.php"); ?>