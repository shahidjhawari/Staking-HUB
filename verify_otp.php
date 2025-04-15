<?php
session_start();
require('db.php'); // آپ کا database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userOtp = $_POST['otp'];

    // چیک کریں کہ session میں otp اور expiry موجود ہیں یا نہیں
    if (isset($_SESSION['otp'], $_SESSION['otp_expiry'])) {
        $storedOtp = $_SESSION['otp'];
        $expiryTime = $_SESSION['otp_expiry'];
        $currentTime = time();

        if ($currentTime > $expiryTime) {
            echo "expired"; // OTP کا وقت ختم ہو گیا
        } elseif ($userOtp === $storedOtp) {
            $_SESSION['email_verified'] = true;
            echo "verified"; // OTP match کر گیا
        } else {
            echo "invalid"; // OTP غلط ہے
        }
    } else {
        echo "expired"; // session میں otp ہی نہیں
    }
}
?>
