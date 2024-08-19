<?php
require('top.inc.php');

// Ensure only admin can access
// if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
//   header("Location: index.php");
//   exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $message = $_POST['message'];

  $stmt = $con->prepare("INSERT INTO announcements (message) VALUES (?)");
  $stmt->bind_param("s", $message);
  $stmt->execute();
  $stmt->close();

  echo "Announcement added successfully.";
}
?>

<form method="post">
  <div class="form-group">
    <label for="message">Message</label>
    <textarea class="form-control" id="message" name="message" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Add Announcement</button>
</form>

<?php require('footer.inc.php'); ?>