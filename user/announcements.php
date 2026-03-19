<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Announcements";
require_login();

$ann = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");

include("../includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Announcements</h1>
      <p class="subtitle">Latest updates from the barangay.</p>
    </div>
  </div>

  <?php if ($ann && mysqli_num_rows($ann) > 0): ?>
    <?php while($a = mysqli_fetch_assoc($ann)): ?>
      <div class="card" style="margin-top:12px;">
        <h3 style="margin:0 0 6px;"><?php echo htmlspecialchars($a['title']); ?></h3>
        <div style="opacity:.8; font-size:12px; margin-bottom:10px;">
          Posted: <?php echo htmlspecialchars($a['created_at']); ?>
        </div>
        <div><?php echo nl2br(htmlspecialchars($a['content'])); ?></div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert">No announcements yet.</div>
  <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>