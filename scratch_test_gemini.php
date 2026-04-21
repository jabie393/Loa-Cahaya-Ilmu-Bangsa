<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = Illuminate\Support\Facades\Http::post('https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key='.env('GEMINI_API_KEY'), [
    'contents' => [
        ['parts' => [['text' => 'Hello']]]
    ]
]);

print_r($res->json());

print_r($res->json());
echo "\nStatus: " . $res->status() . "\n";
echo "Body: " . $res->body() . "\n";
