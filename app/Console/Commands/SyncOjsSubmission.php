<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use App\Services\OjsSubmissionService;
use Illuminate\Support\Facades\Log;

class SyncOjsSubmission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submission:sync-ojs {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync submission to OJS in the background';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $submission = Submission::find($id);

        if (!$submission) {
            $this->error("Submission not found for ID: {$id}");
            return Command::FAILURE;
        }

        try {
            $this->info("Syncing submission ID: {$id} to OJS...");
            $service = app(OjsSubmissionService::class);
            $service->submit($submission);
            $this->info("Successfully synced submission ID: {$id} to OJS.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to sync submission ID: {$id} to OJS: " . $e->getMessage());
            Log::error("CLI OJS Sync failed for submission ID: {$id}. Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
