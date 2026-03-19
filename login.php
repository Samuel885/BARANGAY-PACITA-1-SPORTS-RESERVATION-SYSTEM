<?php
include("config.php");
$page_title = "Login";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared statement prevents SQL Injection
    $stmt = mysqli_prepare($conn, "SELECT id, fullname, role, status, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        if ($user['status'] !== 'active') {
            $error = "Account pending approval. Please wait for admin verification.";
        } else if (!password_verify($password, $user['password'])) {
            $error = "Invalid email or password.";
        } else {
            // Login success - set sessions
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            // Absolute redirects = less bugs
            if ($user['role'] === 'admin') {
                header("Location: /pacita1_reservation/admin/dashboard.php");
            } else {
                header("Location: /pacita1_reservation/dashboard.php");
            }
            exit();
        }

    } else {
        $error = "Invalid email or password.";
    }

    mysqli_stmt_close($stmt);
}

include("includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Welcome Back</h1>
      <p class="subtitle">Login to reserve courts and manage bookings.</p>
    </div>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form class="form" method="POST" autocomplete="on">
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" required>
    </div>

    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required>
    </div>

    <button class="btn btn-primary" type="submit" name="login">
      Login
    </button>
  </form>

  <p class="subtitle" style="margin-top:12px;">
    Don’t have an account? <a class="nav-link" href="/pacita1_reservation/register.php">Register</a>
  </p>
</div>

<?php include("includes/footer.php"); ?>