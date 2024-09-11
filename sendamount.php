<?php
ob_start();
session_start();
require('header.php'); // Ensure this includes your database connection ($conn)

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Adjust 'index.php' to your login page
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form inputs
    $target_username = trim($_POST['username']);
    $amount = trim($_POST['amount']);
    $provided_random_string = trim($_POST['random_string']);
    $fee = $amount * 0.03; // Calculate 3% fee
    $total_deduction = $amount + $fee; // Total amount to deduct

    // Validate inputs
    if (empty($target_username) || empty($amount) || empty($provided_random_string)) {
        echo "Please fill all the fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        echo "Please enter a valid amount.";
    } elseif ($amount < 5) {
        echo "<span style='color: red;'>The minimum amount you can send is $5.</span>";
    } else {
        // Get logged-in user's ID
        $logged_in_user_id = $_SESSION['user_id'];

        // Start a transaction to ensure atomicity
        $conn->begin_transaction();

        try {
            // Fetch the logged-in user's random string
            $sql_user = "SELECT random_string FROM users WHERE id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $logged_in_user_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows === 0) {
                throw new Exception("Logged-in user not found.");
            }

            $user = $result_user->fetch_assoc();
            $user_random_string = $user['random_string'];

            // Verify the provided random string
            if ($user_random_string !== $provided_random_string) {
                throw new Exception("Private Key does not match.");
            }

            // Fetch the logged-in user's current deposit with 'Accepted' status
            $sql_deposit_logged_in = "SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'Accepted' FOR UPDATE";
            $stmt_deposit_logged_in = $conn->prepare($sql_deposit_logged_in);
            $stmt_deposit_logged_in->bind_param("i", $logged_in_user_id);
            $stmt_deposit_logged_in->execute();
            $result_deposit_logged_in = $stmt_deposit_logged_in->get_result();

            if ($result_deposit_logged_in->num_rows === 0) {
                throw new Exception("No deposits found for your account.");
            }

            $deposit_logged_in = $result_deposit_logged_in->fetch_assoc();
            $current_amount_logged_in = $deposit_logged_in['amount'];

            // Check if the logged-in user has enough balance including the 3% fee
            if ($current_amount_logged_in < $total_deduction) {
                throw new Exception("Insufficient balance to cover the amount and fee.");
            }

            // Deduct the total amount (amount + fee) from the logged-in user's deposit
            $new_amount_logged_in = $current_amount_logged_in - $total_deduction;
            $sql_update_logged_in = "UPDATE deposits SET amount = ? WHERE id = ?";
            $stmt_update_logged_in = $conn->prepare($sql_update_logged_in);
            $stmt_update_logged_in->bind_param("di", $new_amount_logged_in, $deposit_logged_in['id']);
            if (!$stmt_update_logged_in->execute()) {
                throw new Exception("Failed to update your deposit.");
            }

            // Fetch the target user's ID based on the provided username
            $sql_target_user = "SELECT id FROM users WHERE username = ?";
            $stmt_target_user = $conn->prepare($sql_target_user);
            $stmt_target_user->bind_param("s", $target_username);
            $stmt_target_user->execute();
            $result_target_user = $stmt_target_user->get_result();

            if ($result_target_user->num_rows === 0) {
                throw new Exception("Target username not found.");
            }

            $target_user = $result_target_user->fetch_assoc();
            $target_user_id = $target_user['id'];

            // Fetch the target user's current deposit with 'Accepted' status
            $sql_deposit_target = "SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'Accepted' FOR UPDATE";
            $stmt_deposit_target = $conn->prepare($sql_deposit_target);
            $stmt_deposit_target->bind_param("i", $target_user_id);
            $stmt_deposit_target->execute();
            $result_deposit_target = $stmt_deposit_target->get_result();

            if ($result_deposit_target->num_rows > 0) {
                // Target user has an existing deposit; update it
                $deposit_target = $result_deposit_target->fetch_assoc();
                $current_amount_target = $deposit_target['amount'];
                $new_amount_target = $current_amount_target + $amount;

                $sql_update_target = "UPDATE deposits SET amount = ? WHERE id = ?";
                $stmt_update_target = $conn->prepare($sql_update_target);
                $stmt_update_target->bind_param("di", $new_amount_target, $deposit_target['id']);
                if (!$stmt_update_target->execute()) {
                    throw new Exception("Failed to update the target user's deposit.");
                }
            } else {
                // Target user does not have an existing deposit; create a new one
                $sql_insert_target = "INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, 'Accepted')";
                $stmt_insert_target = $conn->prepare($sql_insert_target);
                $stmt_insert_target->bind_param("id", $target_user_id, $amount);
                if (!$stmt_insert_target->execute()) {
                    throw new Exception("Failed to create the target user's deposit.");
                }
            }

            // Record the transaction in the `sendamount` table
            $sql_record_transaction = "INSERT INTO sendamount (sender_id, recipient_id, amount, fee, total_deduction) VALUES (?, ?, ?, ?, ?)";
            $stmt_record_transaction = $conn->prepare($sql_record_transaction);
            $stmt_record_transaction->bind_param("iiidd", $logged_in_user_id, $target_user_id, $amount, $fee, $total_deduction);
            if (!$stmt_record_transaction->execute()) {
                throw new Exception("Failed to record the transaction.");
            }

            // Commit the transaction
            $conn->commit();
            echo "Amount successfully deducted (including a 3% fee) from your account and added to " . htmlspecialchars($target_username) . "'s account.";
        } catch (Exception $e) {
            // An error occurred; rollback the transaction
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}

// Fetch all transaction records for display
$sql_records = "SELECT u1.username AS sender, u2.username AS recipient, s.amount, s.fee, s.total_deduction, s.created_at
                FROM sendamount s
                JOIN users u1 ON s.sender_id = u1.id
                JOIN users u2 ON s.recipient_id = u2.id
                ORDER BY s.created_at DESC";
$result_records = $conn->query($sql_records);
?>

<style>
    th,
    td {
        color: white;
    }
</style>

<div class="container my-5">
    <h2 class="text-center">Transfer Amount to Your Friends</h2>
    <form method="POST" action="" class="mt-4">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount (min $5):</label>
            <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
            <div class="form-text text-danger">3% fee will be deducted.</div>
        </div>

        <div class="mb-3">
            <label for="random_string" class="form-label">Your Private Key:</label>
            <input type="text" id="random_string" name="random_string" class="form-control" required>
        </div>

        <div class="text-center">
            <input type="submit" value="Send Amount" class="btn btn-primary">
        </div>
    </form>

    <div class="mt-5">
    <h3 class="text-center">Transaction Records</h3>
    <div class="table-responsive mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sender Username</th>
                    <th>Recipient Username</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Total Deduction</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_records->num_rows > 0): ?>
                    <?php while ($row = $result_records->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['sender']); ?></td>
                            <td><?php echo htmlspecialchars($row['recipient']); ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo number_format($row['fee'], 2); ?></td>
                            <td><?php echo number_format($row['total_deduction'], 2); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
require('footer.php'); // Ensure this includes closing HTML tags and footer content
?>