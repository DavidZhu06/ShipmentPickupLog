<?php
require __DIR__ . '/../vendor/autoload.php'; // PhpSpreadsheet via Composer
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Load DB config
$config = require __DIR__ . '/config.php';
$env = 'production';
$dbConfig = $config[$env];

try {
    $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set timezone
    date_default_timezone_set('America/Vancouver');

    // Prepare filename
    $today = date('Y-m-d');
    $filename = "shipment_log_{$today}.xlsx";
    $filepath = "\\\\BC-FS.idci.local\\Company\\ShipmentLog\\{$filename}";
    $logFile  = "\\\\BC-FS.idci.local\\Company\\ShipmentLog\\shipment_export.log";

    // Query today’s records
    $stmt = $pdo->prepare("SELECT * FROM shipment_log WHERE DATE(submitted_at) = CURDATE()");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "No records found for today.";
        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] No records found.\n",
            FILE_APPEND
        );
        exit;
    }

    // Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set column headers
    $headers = array_keys($rows[0]);
    $sheet->fromArray([$headers], NULL, 'A1');

    // Add data rows
    $sheet->fromArray($rows, NULL, 'A2');

    // Save to file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);

    // Append to log file
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Exported {$filename} (" . count($rows) . " records) to {$filepath}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    echo "Export successful: $filename\n";

} catch (PDOException $e) {
    $errorMsg = "[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    echo "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $errorMsg = "[" . date('Y-m-d H:i:s') . "] Export Error: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    echo "Export Error: " . $e->getMessage();
}
?>
