<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;

class ProcessSubmissionReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submission:process-review {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process AI review for submission in the background';

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
            $this->info("Processing review for submission ID: {$id}...");
            $submission->processReview();
            $this->info("Successfully processed review for submission ID: {$id}.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to process review for submission ID: {$id}: " . $e->getMessage());
            Log::error("CLI Submission Review failed for submission ID: {$id}. Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
