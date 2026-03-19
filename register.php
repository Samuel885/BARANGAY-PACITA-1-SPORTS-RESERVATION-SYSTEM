<?php
include("config.php");
$page_title = "Register";

if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        mysqli_query($conn, "INSERT INTO users (fullname, email, password) VALUES ('$fullname', '$email', '$password')");
        header("Location: login.php");
        exit();
    }
}

include("includes/header.php");
?>

<div class="card">
  <div class="card-header">
    <div>
      <h1 class="title">Create Account</h1>
      <p class="subtitle">Register to reserve courts and join events.</p>
    </div>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form class="form" method="POST">
    <div class="field">
      <label>Full Name</label>
      <input class="input" type="text" name="fullname" required>
    </div>
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required>
    </div>
    <button class="btn btn-primary" type="submit" name="register">Register</button>
  </form>

  <p class="subtitle" style="margin-top:12px;">
    Already have an account? <a class="nav-link" href="login.php">Login</a>
  </p>
</div>

<?php include("includes/footer.php"); ?>