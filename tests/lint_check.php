<?php

$dir = realpath(__DIR__ . '/..');
$appDir = $dir . '/application';

$files = array();
$files[] = $dir . '/index.php';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getRealPath();
    }
}

$errorCount = 0;
foreach ($files as $filePath) {
    $output = array();
    $returnVar = 0;
    exec('php -l "' . $filePath . '" 2>&1', $output, $returnVar);
    if ($returnVar !== 0) {
        echo "LINT ERROR in: " . $filePath . "\n";
        echo implode("\n", $output) . "\n\n";
        $errorCount++;
    }
}

echo "Total PHP files linted: " . count($files) . "\n";
echo "Total syntax errors: " . $errorCount . "\n";
