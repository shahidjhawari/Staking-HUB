<?php
ob_start();
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's current balance and random string
$stmt = $conn->prepare("SELECT SUM(deposits.amount) AS wallet_balance, users.random_string 
                        FROM deposits 
                        JOIN users ON deposits.user_id = users.id 
                        WHERE deposits.user_id = ? AND deposits.status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$wallet_balance = $result['wallet_balance'] ?? 0;
$stored_random_string = $result['random_string'];
$stmt->close();

// Initialize messages
$success_message = $error_message = "";

// Handle the staking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['stake_amount'], $_POST['random_string'])) {
        $stake_amount = $_POST['stake_amount'];
        $input_random_string = $_POST['random_string'];

        // Check if a pending staking request exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS pending_count FROM staking_requests WHERE user_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pending_result = $stmt->get_result()->fetch_assoc();
        $pending_count = $pending_result['pending_count'];
        $stmt->close();

        if ($pending_count > 0) {
            $error_message = "You already have a pending staking request. Wait for approval.";
        } elseif ($input_random_string !== $stored_random_string) {
            $error_message = "You have provided an incorrect private key.";
        } elseif ($stake_amount > 0 && $stake_amount <= $wallet_balance) {
            // Insert staking request
            $stmt = $conn->prepare("INSERT INTO staking_requests (user_id, stake_amount, random_string, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("ids", $user_id, $stake_amount, $input_random_string);
            $stmt->execute();
            $stmt->close();

            $success_message = "Staking request submitted successfully. Awaiting admin approval.";

            // Redirect to prevent form resubmission
            header("Location: staking.php");
            exit();
        } else {
            $error_message = "Invalid staking amount.";
        }
    }
}

// Fetch staking records
$staking_records = [];
$stmt = $conn->prepare("SELECT * FROM stakings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staking_records[] = $row;
}
$stmt->close();

// Fetch staking request records
$staking_requests = [];
$stmt = $conn->prepare("SELECT * FROM staking_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staking_requests[] = $row;
}
$stmt->close();

// Check if there is any pending staking request
$has_pending_request = false;
foreach ($staking_requests as $request) {
    if ($request['status'] === 'pending') {
        $has_pending_request = true;
        break;
    }
}

?>

<style>
    th, td {
        color: white;
    }
</style>

<div class="container">
    <h2>Staking</h2>
    <p>Wallet Balance: $<?php echo htmlspecialchars(number_format($wallet_balance, 2)); ?></p>

    <?php if (!empty($success_message)) : ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="staking.php">
        <div class="form-group">
            <label for="stake_amount">Stake Amount</label>
            <input type="number" placeholder="Enter Stack Amount" class="form-control" id="stake_amount" name="stake_amount" min="0.01" step="0.01" required <?php echo $has_pending_request ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="random_string">Private Key</label>
            <input type="text" placeholder="Enter Private Key" class="form-control" id="random_string" name="random_string" required <?php echo $has_pending_request ? 'disabled' : ''; ?>>
        </div>
        <button type="submit" class="btn btn-primary" <?php echo $has_pending_request ? 'disabled' : ''; ?>>Stake</button>
    </form>

    <?php if ($has_pending_request) : ?>
        <div class="alert alert-warning mt-3">
            You already have a pending staking request. You can stake again once it's approved by the admin.
        </div>
    <?php endif; ?>

    <h3>Staking Records</h3>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Stake Amount</th>
                <th>Status</th>
                <th>Request Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staking_requests as $request) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($request['stake_amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                </tr>
                <?php if ($request['status'] === 'pending') : ?>
                    <tr>
                        <td colspan="4">
                            <div class="alert alert-info">
                                Your staking will start in 48 hours.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require('footer.php'); ?>
