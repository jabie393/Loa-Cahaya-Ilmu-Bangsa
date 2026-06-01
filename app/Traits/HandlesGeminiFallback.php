<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HandlesGeminiFallback
{
    /**
     * Send HTTP request to Gemini API with automatic model fallback and retries.
     *
     * @param string $apiKey
     * @param string $preferredModel
     * @param array $payload
     * @param int $timeout
     * @return \Illuminate\Http::Client\Response
     * @throws \Exception
     */
    protected function callGemini(string $apiKey, string $preferredModel, array $payload, int $timeout = 60)
    {
        $models = [$preferredModel];

        // Define fallback sequence based on the preferred model
        if (
            $preferredModel === 'gemini-flash-latest' || 
            $preferredModel === 'gemini-3.5-flash' || 
            str_contains($preferredModel, '3.5') || 
            str_contains($preferredModel, 'latest')
        ) {
            $models[] = 'gemini-2.5-flash';
            $models[] = 'gemini-2.0-flash';
            $models[] = 'gemini-3.1-flash-lite';
        }

        // Ensure models are unique and empty values are discarded
        $models = array_filter(array_unique($models));

        $lastException = null;
        $response = null;

        foreach ($models as $currentModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$apiKey}";
            
            $maxRetries = 3;
            $retryCount = 0;
            
            while ($retryCount < $maxRetries) {
                try {
                    $response = Http::timeout($timeout)->post($url, $payload);
                    
                    if ($response->successful()) {
                        // If we had to use a fallback model, log a warning so the developer is aware
                        if ($currentModel !== $preferredModel) {
                            Log::warning("Gemini primary model '{$preferredModel}' failed/unavailable. Successfully fell back to stable model '{$currentModel}'.");
                        }
                        return $response;
                    }
                    
                    $status = $response->status();
                    
                    // Handle 404 (model not found/deprecated) or 429/quota limits specifically
                    $isQuotaExceeded = ($status === 429 && str_contains(strtolower($response->body()), 'quota'));
                    
                    if ($status === 404 || $isQuotaExceeded) {
                        Log::warning("Gemini model '{$currentModel}' returned status {$status} (Quota or Deprecation error). Attempting fallback model...", [
                            'response' => $response->body()
                        ]);
                        break; // Break the retry loop for this model to try the next fallback model immediately
                    }
                    
                    // For transient errors (500, 503, 504) or general rate limits, retry after a delay
                    if (in_array($status, [500, 503, 504, 429])) {
                        $retryCount++;
                        if ($retryCount < $maxRetries) {
                            sleep(3);
                            continue;
                        }
                    }
                    
                    break;
                } catch (\Exception $e) {
                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        sleep(3);
                        continue;
                    }
                    $lastException = $e;
                    break;
                }
            }
        }

        // If we exhausted all fallback models and still failed
        if ($response && !$response->successful()) {
            Log::error("All Gemini models failed. Last response body: " . $response->body());
            throw new \Exception("Semua model Gemini mengalami kegagalan. Respons terakhir (Status {$response->status()}): " . $response->body());
        }

        if ($lastException) {
            Log::error("All Gemini models failed due to exceptions. Last exception message: " . $lastException->getMessage());
            throw new \Exception("Semua model Gemini gagal akibat exception. Exception terakhir: " . $lastException->getMessage(), 0, $lastException);
        }

        throw new \Exception("Semua model Gemini gagal tanpa respons atau exception.");
    }
}
