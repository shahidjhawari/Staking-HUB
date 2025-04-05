<?php
ob_start();
require('top.inc.php');

// Start the session to track the form submission state
//session_start();

// Handle toggle request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_signup'])) {
    // Fetch current status of signup
    $result = mysqli_query($con, "SELECT status FROM signup_settings WHERE id = 1");
    $row = mysqli_fetch_assoc($result);
    $currentStatus = $row['status'];

    // Toggle the status
    $newStatus = ($currentStatus === 'enabled') ? 'disabled' : 'enabled';

    // Update the status in the database
    mysqli_query($con, "UPDATE signup_settings SET status = '$newStatus' WHERE id = 1");

    // Set a session variable to avoid status change on refresh
    $_SESSION['status_changed'] = true;

    // Redirect to the same page to prevent resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check if status changed after button click
if (isset($_SESSION['status_changed'])) {
    unset($_SESSION['status_changed']); // Reset the session flag
}

// Get current signup status
$result = mysqli_query($con, "SELECT status FROM signup_settings WHERE id = 1");
$row = mysqli_fetch_assoc($result);
$currentStatus = $row['status'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Signup Settings</h2>
        <p>Signup is currently: <strong style="color: green;"><?php echo strtoupper($currentStatus === 'enabled' ? 'ENABLED' : 'DISABLED'); ?></strong></p>

        <form method="POST">
            <button type="submit" name="toggle_signup" class="btn <?php echo ($currentStatus === 'enabled') ? 'btn-danger' : 'btn-success'; ?>">
                <?php echo ($currentStatus === 'enabled') ? 'Disable Signup' : 'Enable Signup'; ?>
            </button>
        </form>
    </div>
</body>
</html>

<?php require('footer.inc.php'); ?>
