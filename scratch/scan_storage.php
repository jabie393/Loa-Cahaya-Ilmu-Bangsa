<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

function scanDirRecursive($dir) {
    if (!is_dir($dir)) return [];
    $results = [];
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            $results = array_merge($results, scanDirRecursive($path));
        } else {
            $results[] = $path;
        }
    }
    return $results;
}

echo "Storage app contents:\n";
$all = scanDirRecursive(storage_path('app'));
foreach ($all as $p) {
    if (str_contains($p, '.pdf') || str_contains($p, 'temp') || str_contains($p, 'livewire')) {
        echo " - $p\n";
    }
}
