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

<style>
    body {
        background: #070F2B;
        color: white;
    }

    .centered-form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        border: 1px solid #e3e3e3;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background: #141E46;
    }

    .error {
        color: red;
        margin-top: 5px;
    }

    .success {
        color: #0f0;
        margin-top: 5px;
    }

    .password-container {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        top: 75%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        color: black;
    }
</style>

<div class="container">
    <div class="centered-form">
        <div class="form-container">
            <?php if ($signupDisabled): ?>
                <p class="text-danger text-center">Signup limit reached, signup currently disabled.</p>
            <?php else: ?>
                <form action="signup.php" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required autocomplete="new-name">
                    </div>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autocomplete="new-username" minlength="8" maxlength="18">
                        <span class="error"><?php echo $usernameError; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required autocomplete="new-email">
                        <span class="error"><?php echo $emailError; ?></span>
                        <div id="emailMessage" class="mt-2"></div>
                        <button type="button" class="btn btn-primary btn-block mt-3 mb-2" onclick="sendOTP()">Send OTP</button>
                        <div id="otpTimer" class="text-center mt-2" style="font-weight:bold;"></div>


                        <!-- OTP Section hidden by default -->
                        <div id="otpSection" style="display:none;">
                            <input type="text" id="otp" class="form-control mt-3" placeholder="Enter OTP">
                            <button type="button" class="btn btn-success btn-block mt-2" onclick="verifyOTP()">Verify OTP</button>
                            <div id="otpMessage" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="form-group password-container">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="new-password" minlength="8" maxlength="20">
                        <i class="fas fa-eye toggle-password" data-target="password"></i>
                        <span class="error"><?php echo $passwordError; ?></span>
                    </div>
                    <div class="form-group password-container">
                        <label for="confirmPassword">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required autocomplete="new-password" minlength="8" maxlength="20">
                        <i class="fas fa-eye toggle-password" data-target="confirmPassword"></i>
                    </div>
                    <div class="form-group">
                        <label for="referral">Referral Code (optional)</label>
                        <input type="text" class="form-control" id="referral" name="referral" placeholder="Enter referral code" value="<?php echo $referral_code; ?>">
                        <span class="error"><?php echo $referralError; ?></span>
                    </div>
                    <button id="signupBtn" type="submit" class="btn btn-primary btn-block" disabled>Sign Up</button>
                </form>
            <?php endif; ?>
            <div class="text-center mt-3">
                <p>Already have an account? <a href="index.php" class="text-decoration-none">Login</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    function sendOTP() {
    const email = document.getElementById("email").value.trim();
    const messageDiv = document.getElementById("emailMessage");
    const sendBtn = document.querySelector("button[onclick='sendOTP()']");
    const timerDiv = document.getElementById("otpTimer");

    messageDiv.textContent = "";
    messageDiv.className = "";
    timerDiv.textContent = "";

    if (!email) {
        messageDiv.textContent = "Please enter your email first.";
        messageDiv.className = "error";
        return;
    }

    if (!email.endsWith("@gmail.com")) {
        messageDiv.textContent = "Only Gmail addresses are allowed.";
        messageDiv.className = "error";
        return;
    }

    // Disable Send OTP button and start timer
    sendBtn.disabled = true;
    let countdown = 60;
    timerDiv.textContent = `You can resend OTP in ${countdown} seconds`;

    const interval = setInterval(() => {
        countdown--;
        if (countdown > 0) {
            timerDiv.textContent = `You can resend OTP in ${countdown} seconds`;
        } else {
            clearInterval(interval);
            sendBtn.disabled = false;
            timerDiv.textContent = "";
        }
    }, 1000);

    // Send OTP Request
    fetch("sendmail.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "action=send&email=" + encodeURIComponent(email)
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes("OTP Sent")) {
            messageDiv.textContent = "OTP Sent! Please check your Gmail.";
            messageDiv.className = "success";
            document.getElementById("otpSection").style.display = "block";
        } else {
            messageDiv.textContent = data;
            messageDiv.className = "error";
        }
    });
}


    function verifyOTP() {
        const userOtp = document.getElementById("otp").value.trim();
        const messageDiv = document.getElementById("otpMessage");
        messageDiv.textContent = "";
        messageDiv.className = "";

        fetch("sendmail.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=verify&otp=" + encodeURIComponent(userOtp)
            })
            .then(response => response.text())
            .then(data => {
                if (data === "verified") {
                    messageDiv.textContent = "Email Verified ✅";
                    messageDiv.className = "success";
                    document.getElementById("signupBtn").disabled = false;
                    const emailField = document.getElementById("email");
                    emailField.readOnly = true;
                    emailField.style.opacity = "0.6"; // تھوڑا fade کر دیں
                    emailField.style.pointerEvents = "none"; // یوزر کلک بھی نہ کر سکے
                    emailField.style.backgroundColor = "#f0f0f0"; // ہلکا gray background
                    emailField.style.cursor = "not-allowed"; // cursor بھی بدل جائے
                } else if (data === "expired") {
                    messageDiv.textContent = "OTP has expired.";
                    messageDiv.className = "error";
                } else {
                    messageDiv.textContent = "Invalid OTP.";
                    messageDiv.className = "error";
                }
            });
    }

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