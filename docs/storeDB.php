<?php

// Load config
$config = require __DIR__ . '/config.php';
$env = 'production';
$dbConfig = $config[$env];

try {
    $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {


        // Get and sanitize form data (filter_sanitize_string is deprecated as of PHP 8.1)
        $storename = trim($_POST['storename'] ?? ''); 
        $customStore = trim($_POST['customStore'] ?? '');
        $shipref = trim($_POST['shipref'] ?? '');
        $count = trim($_POST['count'] ?? '');
        $id = trim($_POST['id'] ?? '');
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $signature = trim($_POST['signature'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $pickupdate = date('Y-m-d H:i:s');

        // Validate signature format
        if (!str_starts_with($signature, 'data:image/png;base64,')) {
        $signature = ''; // If not valid Base64 PNG image, treat as empty
        }

        // Basic validation
        if (!$storename || !$shipref || !$count || !$firstname || !$lastname || !$signature) {
            die("Missing required fields");
        }

        // Determine the storename to store
        $store_name = $storename;
        if ($storename === "Other") {
            if (empty($customStore)) {
                throw new Exception("Please enter a company name for 'Other'.");
            }
            $store_name = $customStore;
        }

        // Save to database
        $stmt = $pdo->prepare("INSERT INTO shipment_log (storename, shipment_ref, piece_count, carrier_id, first_name, last_name, signature, notes, submitted_at)
                            VALUES (:storename, :shipment_ref, :piece_count, :carrier_id, :first_name, :last_name, :signature, :notes, :submitted_at)");

        $stmt->execute([
            ':storename' => $store_name,
            ':shipment_ref' => $shipref,
            ':piece_count' => $count,
            ':carrier_id' => $id,
            ':first_name' => $firstname,
            ':last_name' => $lastname,
            ':signature' => $signature,
            ':notes' => $notes,
            ':submitted_at' => $pickupdate
        ]);

        // Redirect to endscreen.html on success
        header("Location: endscreen.html");
        exit();
    } 
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>


