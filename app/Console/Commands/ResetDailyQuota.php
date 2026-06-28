<?php

namespace App\Console\Commands;

use App\Services\PlagiarismQuotaService;
use Illuminate\Console\Command;

class ResetDailyQuota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quota:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily review and plagiarism quota for all users at midnight';

    /**
     * Execute the console command.
     */
    public function handle(PlagiarismQuotaService $plagiarismQuotaService)
    {
        $this->info('Resetting daily plagiarism quotas...');
        $plagiarismQuotaService->resetAllDailyQuotas();
        
        $this->info('All daily quotas have been reset successfully.');
    }
}
