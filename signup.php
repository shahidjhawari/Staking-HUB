<?php
ob_start();
require('header.php');

// Debugging - Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if signup is enabled
$signupDisabled = false;
$settingResult = $conn->query("SELECT status FROM signup_settings ORDER BY id DESC LIMIT 1");

// Debugging - Check if the query executed correctly
if ($settingResult) {
    if ($settingResult->num_rows > 0) {
        $row = $settingResult->fetch_assoc();
        if ($row['status'] === 'disabled') {
            $signupDisabled = true;
        }
    } else {
        echo "No settings found.";
    }
} else {
    echo "Error with the query: " . $conn->error;
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateReferralCode($length = 8)
{
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

$emailError = "";
$passwordError = "";
$referralError = "";
$usernameError = "";

$referral_code = isset($_GET['referral']) ? test_input($_GET['referral']) : "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$signupDisabled) {
    $name = test_input($_POST["name"]);
    $username = test_input($_POST["username"]);
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirmPassword = test_input($_POST["confirmPassword"]);
    $referral = test_input($_POST["referral"]);

    if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $usernameError = "Username can only contain letters and numbers.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $usernameError = "Username already exists.";
        }
        $stmt->close();
    }

    if (!preg_match("/@gmail\\.com$/", $email)) {
        $emailError = "Email must be a Gmail address ending with '@gmail.com'.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $emailError = "Email already exists.";
        }
        $stmt->close();
    }

    if ($password != $confirmPassword) {
        $passwordError = "Passwords do not match.";
    } else if (strlen($password) < 8 || strlen($password) > 20) {
        $passwordError = "Password must be between 8 and 20 characters.";
    } else if (empty($usernameError) && empty($emailError)) {
        $referrer_id = null;

        if (!empty($referral)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->bind_param("s", $referral);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($referrer_id);
                $stmt->fetch();
            } else {
                $referralError = "Invalid referral code.";
            }
            $stmt->close();
        }

        if (empty($referralError)) {
            $randomString = bin2hex(random_bytes(50));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $referral_code = generateReferralCode();

            $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, random_string, referrer_id, referral_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssis", $name, $username, $email, $hashed_password, $randomString, $referrer_id, $referral_code);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO rewards (user_id, reward_points, referral_count, level_one_count, level_two_count, level_three_count) VALUES (?, 0, 0, 0, 0, 0)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();

                header("Location: show_key.php?random_string=" . urlencode($randomString));
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
}
?>

<div class="auth-shell">
    <div class="auth-card" style="max-width:460px;">
        <div class="logo-wrap">
            <img src="img/logo2.png" alt="StakingHUB" width="64">
        </div>
        <h4 class="text-center mb-1">Create your account</h4>
        <p class="auth-subtitle">Join StakingHUB and start earning today</p>
        <?php if ($signupDisabled): ?>
            <div class="alert alert-danger text-center">Signup limit reached, signup currently disabled.</div>
        <?php else: ?>
            <form action="signup.php" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required autocomplete="new-name" data-cy="signup-name">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autocomplete="new-username" minlength="8" maxlength="18" data-cy="signup-username">
                    <span class="error-message small"><?php echo $usernameError; ?></span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required autocomplete="new-email" data-cy="signup-email">
                    <span class="error-message small"><?php echo $emailError; ?></span>
                </div>

                <div class="form-group password-container">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="new-password" minlength="8" maxlength="20" data-cy="signup-password">
                    <i class="fas fa-eye toggle-password" data-target="password"></i>
                    <span class="error-message small"><?php echo $passwordError; ?></span>
                </div>
                <div class="form-group password-container">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required autocomplete="new-password" minlength="8" maxlength="20" data-cy="signup-confirm-password">
                    <i class="fas fa-eye toggle-password" data-target="confirmPassword"></i>
                </div>
                <div class="form-group">
                    <label for="referral">Referral Code (optional)</label>
                    <input type="text" class="form-control" id="referral" name="referral" placeholder="Enter referral code" value="<?php echo $referral_code; ?>" data-cy="signup-referral">
                    <span class="error-message small"><?php echo $referralError; ?></span>
                </div>
                <button id="signupBtn" type="submit" class="btn btn-primary btn-block" data-cy="signup-submit">Sign Up</button>
            </form>
        <?php endif; ?>
        <div class="text-center mt-4" style="color:var(--text-3);">
            <p class="mb-0">Already have an account? <a href="index.php">Login</a></p>
        </div>
    </div>
</div>

<script>
    // Show/Hide Password
    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            const target = document.getElementById(this.getAttribute('data-target'));
            if (target.type === 'password') {
                target.type = 'text';
                this.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.type = 'password';
                this.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>


<?php require('footer.php'); ?>