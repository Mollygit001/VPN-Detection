<?php
// Function to load environment variables from .env file
function loadEnv($filePath = __DIR__ . '/.env') {
    if (!file_exists($filePath)) return;

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Load environment variables
loadEnv();

// Get the API key from the environment
$apiKey = $_ENV['IPHUB_API_KEY'];

// Include the IPHub class file (you need to download and include it if not using Composer)
require_once('iphub.class.php');

// Use the Lookup class from the IPHub namespace and alias it as IPHub
use IPHub\Lookup as IPHub;

// Get the client's IP address from the server superglobal
$ip = $_SERVER['REMOTE_ADDR'];

// Initialize a variable to store the access state
$accessState = '';

// Prepare result variable for user input IP check
$result = [];

// Handling the initial IP check
try {
    // Call the isBadIP method of the IPHub class to check if the IP is bad
    $block = IPHub::isBadIP($ip, $apiKey);

    // If the IP is bad
    if ($block) {
        $accessState = 'blocked';
    } else {
        $accessState = 'granted';
    }
} catch (Exception $e) {
    $accessState = 'error';
    echo 'An error occurred: ' . $e->getMessage();
}

try {
    // Get detailed IP information
    $ipInfo = IPHub::getIPInfo($ip, $apiKey);
} catch (Exception $e) {
    echo 'An error occurred: ' . $e->getMessage();
}

// Handling POST request to check another IP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedIp = $_POST['ipAddress'] ?? '';

    if (!empty($submittedIp)) {
        try {
            $block = IPHub::isBadIP($submittedIp, $apiKey);
            $accessState = $block ? 'blocked' : 'granted';

            // Get detailed IP information for the submitted IP
            $ipInfo = IPHub::getIPInfo($submittedIp, $apiKey);

            // Prepare data to pass to the HTML
            $result = [
                'submittedIp' => $submittedIp,
                'accessState' => $accessState,
                'ipInfo' => $ipInfo,
            ];
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage()];
        }
    }
}

// Include HTML template and pass data
include('template.html');
?>
