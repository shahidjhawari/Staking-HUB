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
            'sender' => ['name' => 'STAKINGHUB', 'email' => 'stakinghub8@gmail.com'],
            'to' => [['email' => $email]],
            'subject' => 'Your OTP Code',
            'htmlContent' => "
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f9f9f9;
                            color: #333;
                            padding: 20px;
                            text-align: center;
                        }
                        .container {
                            background-color: #ffffff;
                            padding: 40px;
                            border-radius: 10px;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            max-width: 600px;
                            margin: auto;
                        }
                        h4 {
                            color:rgb(8, 0, 121);
                            font-size: 20px;
                            margin-bottom: 10px;
                        }
                        p {
                            font-size: 16px;
                            margin: 10px 0;
                        }
                        .otp {
                            font-size: 36px;
                            font-weight: bold;
                            color:rgb(0, 0, 0);
                            margin: 20px 0;
                        }
                        .footer {
                            margin-top: 40px;
                            font-size: 12px;
                            color: #999;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h4>Welcome to StakingHub</h4>
                        <p>Thank you for visiting our website. We appreciate your presence.</p>
                        <p style='margin-top: 50px'>Your OTP code is:</p>
                        <div class='otp'>$otp</div>
                        <div class='footer'>
                            &copy; " . date('Y') . " Staking Hub. All rights reserved.
                        </div>
                    </div>
                </body>
                </html>
            "
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
