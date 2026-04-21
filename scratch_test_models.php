<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pageToken = '';
$models = [];
do {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models?key='.env('GEMINI_API_KEY');
    if ($pageToken) {
        $url .= '&pageToken='.$pageToken;
    }
    $res = Illuminate\Support\Facades\Http::get($url);
    $data = $res->json();
    if (isset($data['models'])) {
        foreach ($data['models'] as $m) {
            if (isset($m['name'])) {
                $models[] = $m['name'];
            }
        }
    }
    $pageToken = $data['nextPageToken'] ?? '';
} while ($pageToken);

foreach ($models as $name) {
    if (strpos($name, 'flash') !== false && strpos($name, 'latest') === false) {
        echo $name . "\n";
    }
}
