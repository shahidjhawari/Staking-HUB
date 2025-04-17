<?php
ob_start();
session_start();
require('header.php'); // اس میں $conn موجود ہونا چاہیے (DB connection)

function test_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

if (!isset($_SESSION['fp_email'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Unauthorized access. No email session found.</div></div>";
    exit();
}

$email = $_SESSION['fp_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = test_input($_POST["new_password"]);
    $confirm_password = test_input($_POST["confirm_password"]);

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else if (strlen($new_password) < 8 || strlen($new_password) > 20) {
        $error_message = "Password must be between 8 and 20 characters.";
    } else {
        // ✅ Check if email exists in users table
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($username);
            $stmt->fetch();
            $stmt->close();

            // ✅ Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // ✅ Update the user's password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            $stmt->execute();
            $stmt->close();

            // ✅ Clear session values (for security)
            unset($_SESSION['fp_email']);
            unset($_SESSION['fp_otp']);
            unset($_SESSION['fp_otp_expiry']);

            $success_message = "Your password has been reset successfully.";
        } else {
            $error_message = "Email not found in database.";
        }
    }
}
?>

<style>
    body {
        background: #f8f9fa;
        color: #333;
    }

    .container {
        margin-top: 50px;
    }

    .alert {
        margin-top: 20px;
    }
</style>

<div class="container">
    <h2 class="text-center">Reset Password</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" id="backToLoginBtn" class="btn btn-success">Now Back to Login</a>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" maxlength="20">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const successAlert = document.querySelector('.alert-success');
        const backToLoginBtn = document.getElementById('backToLoginBtn');

        if (successAlert && backToLoginBtn) {
            // تھوڑا smooth effect دینے کے لیے
            backToLoginBtn.style.display = "none";
            setTimeout(() => {
                backToLoginBtn.style.display = "inline-block";
            }, 500); // 0.5 سیکنڈ بعد بٹن ظاہر ہوگا
        }
    });
</script>
