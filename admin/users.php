<?php
include("../config.php");
include("../includes/auth.php");

$page_title = "Manage Users";
require_admin();

/* Activate user */
if (isset($_GET['activate'])) {
    $id = intval($_GET['activate']);
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$id");
}

/* Deactivate user */
if (isset($_GET['deactivate'])) {
    $id = intval($_GET['deactivate']);
    mysqli_query($conn, "UPDATE users SET status='pending' WHERE id=$id");
}

/* Delete user */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

include("../includes/header.php");
?>

<div class="card">

<div class="card-header">
<h1 class="title">Manage Users</h1>
<p class="subtitle">Approve, deactivate, or remove resident accounts.</p>
</div>

<table class="table">

<tr>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($users)): ?>

<tr>

<td><?php echo htmlspecialchars($row['fullname']); ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo $row['role']; ?></td>

<td>
<span class="badge <?php echo $row['status']; ?>">
<?php echo $row['status']; ?>
</span>
</td>

<td>

<?php if ($row['status'] == "pending"): ?>

<a class="btn btn-primary"
href="?activate=<?php echo $row['id']; ?>">
Activate
</a>

<?php else: ?>

<a class="btn btn-danger"
href="?deactivate=<?php echo $row['id']; ?>">
Deactivate
</a>

<?php endif; ?>

<a class="btn btn-danger"
href="?delete=<?php echo $row['id']; ?>"
onclick="return confirm('Delete this user?')">
Delete
</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

<?php include("../includes/footer.php"); ?>