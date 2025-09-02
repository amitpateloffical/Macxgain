<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchTrueDataJob;

class PopulateTrueDataCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truedata:populate-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate TrueData cache with live market data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating TrueData cache...');
        
        // Dispatch the job to fetch data
        FetchTrueDataJob::dispatch();
        
        $this->info('TrueData cache population job dispatched successfully!');
        $this->info('Cache will be populated with live market data.');
        
        return 0;
    }
}