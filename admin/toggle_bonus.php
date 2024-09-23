<?php
require('top.inc.php');

// Redirect to login page if not logged in
// if (!isset($_SESSION['admin_id'])) {
//     echo "<script>window.location.href = 'index.php';</script>";
//     exit();
// }

$config_path = '../config.php'; // Define the path to config.php
$config = include($config_path); // Include the config file
$bonus_enabled = $config['bonus_enabled'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Toggle the bonus setting
    $bonus_enabled = !$bonus_enabled;

    // Update the configuration file
    $new_config = "<?php\nreturn [\n    'bonus_enabled' => " . ($bonus_enabled ? 'true' : 'false') . ",\n];\n";

    if (file_put_contents($config_path, $new_config) !== false) {
        $message = $bonus_enabled ? "Bonus feature enabled." : "Bonus feature disabled.";
    } else {
        $message = "Failed to update the configuration file.";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center">Toggle Bonus Feature</h2>
    <form method="POST" class="text-center">
        <button type="submit" class="btn btn-info">
            <?php echo $bonus_enabled ? "Disable Bonus" : "Enable Bonus"; ?>
        </button>
    </form>
    <?php if (isset($message)): ?>
        <div class="alert alert-success mt-3"><?php echo $message; ?></div>
    <?php endif; ?>
</div>

<?php require('footer.inc.php'); ?>