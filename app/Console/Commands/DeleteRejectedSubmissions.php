<?php

namespace App\Console\Commands;

use App\Models\Submission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteRejectedSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submissions:delete-rejected';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete submissions that have been rejected for 7 days or more';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get submissions that were rejected 7 days ago or longer
            $sevenDaysAgo = now()->subDays(7);

            $submissions = Submission::where('status', 'Rejected')
                ->where('rejected_date', '<=', $sevenDaysAgo)
                ->get();

            $deletedCount = 0;

            foreach ($submissions as $submission) {
                if ($submission->proof_of_payment) {
                    Storage::disk('public')->delete($submission->proof_of_payment);
                }

                $submission->delete();
                $deletedCount++;
            }

            $message = "Deleted {$deletedCount} rejected submissions older than 7 days";

            $this->info($message);
            Log::info($message);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $errorMessage = "Error deleting rejected submissions: " . $e->getMessage();

            $this->error($errorMessage);
            Log::error($errorMessage);

            return self::FAILURE;
        }
    }
}
