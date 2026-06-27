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
        $payload = $this->sanitizePayloadUtf8($payload);

        $apiKeys = array_filter(array_map('trim', explode(',', $apiKey)));
        if (empty($apiKeys)) {
            throw new \Exception("API Key Gemini belum diatur atau kosong.");
        }

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

        foreach ($apiKeys as $keyIndex => $currentKey) {
            foreach ($models as $currentModel) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$currentKey}";
                
                $maxRetries = 3;
                $retryCount = 0;
                
                while ($retryCount < $maxRetries) {
                    try {
                        $response = Http::timeout($timeout)->post($url, $payload);
                        
                        if ($response->successful()) {
                            // Log warning if fallback model or fallback key was used
                            if ($currentModel !== $preferredModel) {
                                Log::warning("Gemini primary model '{$preferredModel}' failed/unavailable. Successfully fell back to stable model '{$currentModel}'.");
                            }
                            if ($keyIndex > 0) {
                                Log::warning("Gemini primary API key failed/exhausted. Successfully fell back to key index {$keyIndex}.");
                            }
                            return $response;
                        }
                        
                        $status = $response->status();
                        
                        // Handle rate limits (429) or forbidden/unauthorized (403) specifically as key-exhaustion/limit errors
                        $isQuotaOrLimit = ($status === 429 || $status === 403);
                        
                        if ($isQuotaOrLimit) {
                            Log::warning("Gemini API key index {$keyIndex} returned status {$status} (Quota/Limit error). Attempting fallback to the next API key...", [
                                'response' => $response->body()
                            ]);
                            break 2; // Break both retry and models loop, move to next API key
                        }
                        
                        // Handle 404 (model not found/deprecated)
                        if ($status === 404) {
                            Log::warning("Gemini model '{$currentModel}' returned status 404 (Not Found). Attempting fallback model on same key...", [
                                'response' => $response->body()
                            ]);
                            break; // Break the retry loop for this model to try the next fallback model
                        }
                        
                        // For transient errors (500, 503, 504), retry after a delay
                        if (in_array($status, [500, 503, 504])) {
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
        }

        // If we exhausted all fallback keys/models and still failed
        if ($response && !$response->successful()) {
            Log::error("All Gemini API keys and models failed. Last response body: " . $response->body());
            throw new \Exception("Semua model dan API Key Gemini mengalami kegagalan. Respons terakhir (Status {$response->status()}): " . $response->body());
        }

        if ($lastException) {
            Log::error("All Gemini API keys and models failed due to exceptions. Last exception message: " . $lastException->getMessage());
            throw new \Exception("Semua model dan API Key Gemini gagal akibat exception. Exception terakhir: " . $lastException->getMessage(), 0, $lastException);
        }

        throw new \Exception("Semua model dan API Key Gemini gagal tanpa respons atau exception.");
    }

    /**
     * Recursively sanitizes payload arrays to ensure all strings are valid UTF-8.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizePayloadUtf8($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            }
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizePayloadUtf8($value);
            }
            return $data;
        }

        return $data;
    }

    /**
     * Clean and decode JSON response from Gemini, repairing common issues.
     *
     * @param string $rawContent
     * @return array
     * @throws \Exception
     */
    protected function decodeGeminiJson(string $rawContent): array
    {
        $rawContent = trim($rawContent);
        
        // Strip markdown code block wrappers if present
        if (str_starts_with($rawContent, '```')) {
            $rawContent = preg_replace('/^```(?:json)?\s+/', '', $rawContent);
            $rawContent = preg_replace('/\s+```$/', '', $rawContent);
            $rawContent = trim($rawContent);
        }

        $result = json_decode($rawContent, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        // Log initial parsing failure
        Log::warning("Gemini JSON Parsing failed. Error: " . json_last_error_msg() . " | Attempting repair...");

        // 1. Repair missing commas between key-value pairs
        $repaired = preg_replace('/("|\d|true|false|null|\]|\})(\s*\n\s*)"([^"]+)":/i', '$1,$2"$3":', $rawContent);

        // 2. Try parsing again
        $result = json_decode($repaired, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        // 3. Strip control characters as last resort
        $clean = preg_replace('/[\x00-\x1F\x7F]/', '', $repaired);
        $result = json_decode($clean, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        // 4. If still failing, throw exception with details
        Log::error("Failed to parse repaired Gemini JSON: " . json_last_error_msg() . " | Raw Content: " . $rawContent);
        throw new \Exception("Gagal memparsing JSON dari AI: " . json_last_error_msg() . " | Raw Content: " . mb_substr($rawContent, 0, 150) . "...");
    }
}
