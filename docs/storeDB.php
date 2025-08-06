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
        $storename = filter_input(INPUT_POST, 'storename', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['storename']), ENT_QUOTES, 'UTF-8') : '';
        $customStore = filter_input(INPUT_POST, 'customStore', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['customStore']), ENT_QUOTES, 'UTF-8') : '';
        $shipref = filter_input(INPUT_POST, 'shipref', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['shipref']), ENT_QUOTES, 'UTF-8') : '';
        $count = filter_input(INPUT_POST, 'count', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['count']), ENT_QUOTES, 'UTF-8') : '';
        $id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['id']), ENT_QUOTES, 'UTF-8') : '';
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['firstname']), ENT_QUOTES, 'UTF-8') : '';
        $lastname = filter_input(INPUT_POST, 'lastname', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['lastname']), ENT_QUOTES, 'UTF-8') : '';
        $signature = filter_input(INPUT_POST, 'signature', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['signature']), ENT_QUOTES, 'UTF-8') : '';
        $notes = filter_input(INPUT_POST, 'notes', FILTER_DEFAULT) ? htmlspecialchars(trim($_POST['notes']), ENT_QUOTES, 'UTF-8') : '';
        $pickupdate = date('Y-m-d H:i:s');

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


