<?php
// Load the library installed via Composer
require_once __DIR__ . '/vendor/autoload.php'; // Ensure "vendor" folder and autoload.php exist

// Use Sendinblue's API classes
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;

// Insert your API Key here
$apiKey = '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';

// Set up the configuration for the Sendinblue API
$config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);

// Create an instance of the API
$apiInstance = new TransactionalEmailsApi(null, $config);

// Prepare the email data
$sendSmtpEmail = new SendSmtpEmail([
    'subject' => 'Email Verification',
    'sender' => ['name' => 'NAWAB', 'email' => 'shahidjhawari@gmail.com'],
    'to' => [['email' => 'nawabytchannel@gmail.com', 'name' => 'User']],
    'htmlContent' => '<p>Welcome to NAWAB ACADEMY</p>',
]);

// Try sending the email
try {
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    echo "Email sent Shedi successfully!";
} catch (Exception $e) {
    echo 'Failed to send email: ', $e->getMessage();
}
?>
