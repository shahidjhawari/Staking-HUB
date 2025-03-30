<?php
// Detect if running on localhost
$localhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

// Localhost Connection
if ($localhost) {
    $conn = mysqli_connect("localhost", "root", "", "coin");
    define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/nawab/');
    define('SITE_PATH', 'http://localhost/nawab/');
} else {
    // Online Connection (Choose one of your hosting options)
    $host = $_SERVER['HTTP_HOST'];
    
    if (strpos($host, 'infinityfree.com') !== false) {
        // First Online
        $conn = mysqli_connect("sql206.infinityfree.com", "if0_36853949", "ZkQAKes45YF34", "if0_36853949_coin");
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
        define('SITE_PATH', $protocol . $host . '/');
    } else {
        // Original Online
        $conn = mysqli_connect("sql213.infinityfree.com", "if0_37082220", "bX5rPVvrqf0SDv", "if0_37082220_coin");
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
        define('SITE_PATH', $protocol . $host . '/');
    }
}

// Common Paths
define('PRODUCT_IMAGE_SERVER_PATH', SERVER_PATH . 'media/product/');
define('PRODUCT_IMAGE_SITE_PATH', SITE_PATH . 'media/product/');

// Optional: Check Database Connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
