<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Utils\DataImporter;



// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Get JSON file path from argument or use default
$jsonPath = $argv[1] ?? __DIR__ . '/../data/data.json';
if (!file_exists($jsonPath)) {
    echo "Error: File not found: {$jsonPath}\n";
    exit(1);
}

echo "Starting import from: {$jsonPath}\n";
echo "----------------------------------------\n";

try {
    $importer = new DataImporter();
    $results = $importer->import($jsonPath);

    echo "Import completed!\n";
    echo "Categories imported: {$results['categories']}\n";
    echo "Products imported: {$results['products']}\n";

    if (!empty($results['errors'])) {
        echo "\nErrors:\n";
        foreach ($results['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
} catch (\Exception $e) {
    echo "Import failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
