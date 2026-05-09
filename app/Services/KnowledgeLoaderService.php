<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class KnowledgeLoaderService
{
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('app/ai');
    }

    public function loadSystemPrompt(): string
    {
        $file = $this->path . '/system_prompt.txt';
        if (File::exists($file)) {
            return trim(File::get($file));
        }
        return "Kamu adalah AI Assistant.";
    }

    public function loadKnowledgeBase(): string
    {
        if (!File::exists($this->path)) {
            return "";
        }

        $knowledge = "";
        $files = File::files($this->path);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $filename = $file->getFilename();

            // Skip system_prompt as it's loaded separately
            if ($filename === 'system_prompt.txt') {
                continue;
            }

            if (in_array($extension, ['txt', 'md'])) {
                $knowledge .= "\n--- SOURCE: {$filename} ---\n";
                $knowledge .= trim(File::get($file->getRealPath()));
                $knowledge .= "\n-----------------------------\n";
            }
        }

        return trim($knowledge);
    }
}
