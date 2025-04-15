<?php
session_start();

// Brevo API settings
$apiKey = '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'; // ← یہاں اپنی API Key دیں

// Handle requests based on action
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === "send") {
        $email = $_POST['email'] ?? '';
        if (!preg_match("/@gmail\.com$/", $email)) {
            echo "Only Gmail addresses allowed.";
            exit;
        }

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes

        $data = [
            'sender' => ['name' => 'NAWAB', 'email' => 'shahidjhawari@gmail.com'],
            'to' => [['email' => $email]],
            'subject' => 'Your OTP Code',
            'htmlContent' => "<p>Your OTP code is: <strong>$otp</strong></p>"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            echo "OTP Sent";
        } else {
            echo "Failed to send OTP.";
        }

    } elseif ($action === "verify") {
        $userOtp = $_POST['otp'] ?? '';
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
            echo "expired";
            exit;
        }

        if (time() > $_SESSION['otp_expiry']) {
            echo "expired";
        } elseif ($userOtp == $_SESSION['otp']) {
            $_SESSION['email_verified'] = true;
            echo "verified";
        } else {
            echo "invalid";
        }
    } else {
        echo "Invalid action.";
    }
}
?>
