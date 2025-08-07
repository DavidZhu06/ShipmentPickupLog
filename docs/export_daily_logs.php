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

    // Query today’s records
    $stmt = $pdo->prepare("SELECT * FROM shipment_log WHERE DATE(submitted_at) = CURDATE()");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "No records found for today.";
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

    echo "Export successful: $filename\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Export Error: " . $e->getMessage();
}
?>
