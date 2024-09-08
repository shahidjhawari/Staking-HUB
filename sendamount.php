<?php
ob_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $amount = $_POST['amount'];
    $random_string = $_POST['random_string'];

    // Check if all fields are filled
    if (empty($username) || empty($amount) || empty($random_string)) {
        echo "Please fill all the fields.";
    } else {
        // First, check if the user exists and the random string matches
        $sql = "SELECT id, random_string FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the random string
            if ($user['random_string'] === $random_string) {
                // Now check the amount in the deposits table for the logged-in user
                $user_id = $_SESSION['user_id'];
                $sql_deposit = "SELECT amount FROM deposits WHERE user_id = ? AND status = 'Accepted'";
                $stmt_deposit = $conn->prepare($sql_deposit);
                $stmt_deposit->bind_param("i", $user_id);
                $stmt_deposit->execute();
                $result_deposit = $stmt_deposit->get_result();

                if ($result_deposit->num_rows > 0) {
                    $deposit = $result_deposit->fetch_assoc();
                    $current_amount = $deposit['amount'];

                    // Check if the user has enough balance
                    if ($current_amount >= $amount) {
                        // Deduct the amount
                        $new_amount = $current_amount - $amount;
                        $sql_update = "UPDATE deposits SET amount = ? WHERE user_id = ? AND status = 'Accepted'";
                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->bind_param("di", $new_amount, $user_id);

                        if ($stmt_update->execute()) {
                            // Insert new deposit record for the specified user
                            $sql_insert = "INSERT INTO deposits (user_id, amount, status) VALUES ((SELECT id FROM users WHERE username = ?), ?, 'Accepted')";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bind_param("sd", $username, $amount);

                            if ($stmt_insert->execute()) {
                                echo "Amount successfully deducted and recorded.";
                            } else {
                                echo "Failed to insert the new deposit record.";
                            }
                        } else {
                            echo "Failed to update the deposit amount.";
                        }
                    } else {
                        echo "Insufficient balance.";
                    }
                } else {
                    echo "No deposits found for this user.";
                }
            } else {
                echo "Random string does not match.";
            }
        } else {
            echo "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deduct Amount</title>
</head>
<body>
    <h2>Deduct Amount from Deposit</h2>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="amount">Amount to Deduct:</label>
        <input type="number" id="amount" name="amount" step="0.01" required><br><br>
        
        <label for="random_string">Random String:</label>
        <input type="text" id="random_string" name="random_string" required><br><br>
        
        <input type="submit" value="Submit">
    </form>
</body>
</html>
