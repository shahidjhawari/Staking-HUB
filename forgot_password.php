<?php
session_start();


// ✅ Optional: include('header.php'); اگر تمہیں database یا styles کی ضرورت ہے

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// ✅ Brevo API Configuration
$brevo_api_key = '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';
$sender_email = 'stakinghub8@gmail.com'; // تمہارا verified sender on Brevo

// ✅ Send OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] === 'send') {
    $email = test_input($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, "@gmail.com")) {
        echo "Only valid Gmail addresses allowed.";
        exit();
    }

    $otp = rand(100000, 999999);
    $_SESSION['fp_email'] = $email;
    $_SESSION['fp_otp'] = (string)$otp;
    $_SESSION['fp_otp_expiry'] = time() + 300;

    // ✅ Send via Brevo API
    $data = [
        "sender" => ["name" => "STAKINGHUB", "email" => $sender_email],
        "to" => [["email" => $email]],
        "subject" => "Your OTP for Password Reset",
        "htmlContent" => "<p>Your OTP is: <strong>$otp</strong></p><p>This OTP is valid for 5 minutes.</p>"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $brevo_api_key",
        "content-type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo ($http_code == 201) ? "OTP Sent" : "Failed to send OTP. Please try again.";
    exit();
}

// ✅ Verify OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] === 'verify') {
    $email = test_input($_POST["email"]);
    $otp = trim($_POST["otp"]);

    if (!isset($_SESSION['fp_email'], $_SESSION['fp_otp'], $_SESSION['fp_otp_expiry'])) {
        echo "not_found";
    } elseif ($_SESSION['fp_email'] !== $email) {
        echo "mismatch";
    } elseif (time() > $_SESSION['fp_otp_expiry']) {
        echo "expired";
    } elseif ($otp === $_SESSION['fp_otp']) {
        $reset_token = bin2hex(random_bytes(32));
        $_SESSION['reset_token'] = $reset_token;
        echo "redirect:reset_password.php?token=" . urlencode($reset_token);
    } else {
        echo "invalid";
    }
    exit();
}
?>

<!-- ✅ HTML Starts -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | StakingHUB</title>
    <link rel="icon" href="img/logo2.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/glass-theme.css">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="logo-wrap">
            <img src="img/logo2.png" alt="StakingHUB" width="64">
        </div>
        <h4 class="text-center mb-1">Forgot Password</h4>
        <p class="auth-subtitle">We'll email you a one-time code to reset it</p>
        <form id="forgotForm" onsubmit="return false;">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="you@gmail.com" required>
                <div id="emailMessage" class="mt-2 small"></div>
                <button type="button" id="sendOtpBtn" class="btn btn-primary btn-block mt-3" onclick="sendOTP()">Send OTP</button>
            </div>
            <div id="otpSection" style="display:none;">
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="text" class="form-control" id="otp" placeholder="6-digit code" required>
                    <div id="otpMessage" class="mt-2 small"></div>
                    <button type="button" class="btn btn-secondary btn-block mt-3" onclick="verifyOTP()">Verify OTP</button>
                </div>
            </div>
        </form>
        <div class="text-center mt-4" style="color:var(--text-3);">
            <p class="mb-0"><a href="index.php">Back to Login</a></p>
        </div>
    </div>
</div>

<!-- ✅ JavaScript -->
<script>
    function sendOTP() {
        const email = document.getElementById("email").value.trim();
        const emailMessage = document.getElementById("emailMessage");

        if (!email.endsWith("@gmail.com")) {
            emailMessage.textContent = "Only Gmail addresses are allowed.";
            emailMessage.className = "text-danger";
            return;
        }

        fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=send&email=" + encodeURIComponent(email)
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("OTP Sent")) {
                emailMessage.textContent = "OTP sent successfully. Please check your Gmail.";
                emailMessage.className = "text-success";
                document.getElementById("otpSection").style.display = "block";
                startOTPTimer();
            } else {
                emailMessage.textContent = data;
                emailMessage.className = "text-danger";
            }
        });
    }

    function verifyOTP() {
        const email = document.getElementById("email").value.trim();
        const otp = document.getElementById("otp").value.trim();
        const otpMessage = document.getElementById("otpMessage");

        fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=verify&email=" + encodeURIComponent(email) + "&otp=" + encodeURIComponent(otp)
        })
        .then(response => response.text())
        .then(data => {
            if (data.startsWith("redirect:")) {
                window.location.href = data.replace("redirect:", "");
            } else if (data === "expired") {
                otpMessage.textContent = "OTP has expired.";
                otpMessage.className = "text-danger";
            } else if (data === "invalid") {
                otpMessage.textContent = "Invalid OTP.";
                otpMessage.className = "text-danger";
            } else if (data === "mismatch") {
                otpMessage.textContent = "Email doesn't match the session.";
                otpMessage.className = "text-danger";
            } else {
                otpMessage.textContent = "Verification failed.";
                otpMessage.className = "text-danger";
            }
        });
    }

    function startOTPTimer() {
        const button = document.getElementById("sendOtpBtn");
        let timeLeft = 60;
        button.disabled = true;

        const timer = setInterval(() => {
            button.textContent = `Resend OTP in ${timeLeft}s`;
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(timer);
                button.textContent = "Send OTP";
                button.disabled = false;
            }
        }, 1000);
    }
</script>
</body>
</html>
