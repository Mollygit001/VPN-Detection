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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IPHub VPN Detection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nova+Square">
    <style>
        body {
            font-family: "Nova Square";
            background-image: url("gilgeekify.jpg");
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            <?= ($accessState === 'blocked') ? 'backdrop-filter: grayscale(1);' : ''; ?>
        }

        h1 {
            text-align: center;
            padding: 40px 0;
            font-size: 24px;
            font-weight: 900;
        }

        .blocked {
            color: #fff;
            background-color: #ff0000e6;
        }

        .granted {
            color: #b5ff00;
            background-color: #111111e6;
        }

        span {
            display: block;
        }

        .form-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="text"] {
            padding: 10px;
            width: 250px;
            font-size: 16px;
            margin-right: 10px;
        }

        button {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #111;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <?php if ($accessState === 'blocked') : ?>
        <h1 class="animate__animated animate__flipInY animate__slower blocked">
            <span class="animate__animated animate__pulse animate__infinite animate__slower">
                Request blocked. You appear to be browsing<br>
                from a VPN/Proxy/Server.<br>
                Your IP address is: <?= $ip; ?>
            </span>
        </h1>
    <?php elseif ($accessState === 'granted') : ?>
        <h1 class="animate__animated animate__flipInY animate__slower granted">
            <span class="animate__animated animate__pulse animate__infinite animate__slower">
                Welcome! Your IP is clear for access.
            </span>
        </h1>
    <?php else : ?>
        <h1>Error: Could not determine access state</h1>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <label for="ipAddress">Check another IP:</label>
            <input type="text" id="ipAddress" name="ipAddress" placeholder="Enter IP address">
            <button type="submit">Check</button>
        </form>
    </div>

    <pre>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedIp = $_POST['ipAddress'] ?? '';

            if (!empty($submittedIp)) {
                try {
                    $block = IPHub::isBadIP($submittedIp, $apiKey);
                    $accessState = $block ? 'blocked' : 'granted';

                    echo "Checking IP: $submittedIp\n";
                    echo "Access State: $accessState\n";

                    // Get detailed IP information for the submitted IP
                    $ipInfo = IPHub::getIPInfo($submittedIp, $apiKey);
                    echo "IP Info:\n";
                    print_r($ipInfo);
                } catch (Exception $e) {
                    echo 'An error occurred: ' . $e->getMessage();
                }
            }
        }
        ?>
    </pre>
</body>
</html>
