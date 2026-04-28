<?php

namespace App\Console\Commands;

use App\Services\QuotaService;
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
    protected $description = 'Reset daily review quota for all users at midnight';

    /**
     * Execute the console command.
     */
    public function handle(QuotaService $quotaService)
    {
        $this->info('Resetting daily quotas...');
        
        $quotaService->resetAllDailyQuotas();
        
        $this->info('Daily quotas have been reset successfully.');
    }
}
