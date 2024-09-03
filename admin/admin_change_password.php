<?php
ob_start();
require('top.inc.php');

$password_message = ""; // Variable to hold messages for the admin

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  // Validate new password and confirm password
  if ($new_password !== $confirm_password) {
    $password_message = "New password and confirm password do not match.";
  } else {
    // Update the password in the database (in plain text or use another hashing method if needed)
    $stmt = $con->prepare("UPDATE admin_users SET password = ?");
    $stmt->bind_param("s", $new_password);
    $stmt->execute();
    $stmt->close();

    $password_message = "Password updated successfully.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Change Password</title>
</head>
<body>
  <h2>Change Password</h2>
  <form method="POST" action="">
    <label for="new_password">New Password:</label><br>
    <input type="password" id="new_password" name="new_password" required><br><br>

    <label for="confirm_password">Confirm New Password:</label><br>
    <input type="password" id="confirm_password" name="confirm_password" required><br><br>

    <input type="submit" value="Change Password">
  </form>

  <?php if ($password_message != ""): ?>
    <p><?php echo $password_message; ?></p>
  <?php endif; ?>
</body>
</html>
