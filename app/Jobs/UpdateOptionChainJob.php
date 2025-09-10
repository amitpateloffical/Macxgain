<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\OptionsService;

class UpdateOptionChainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $symbols;
    protected $expiry;

    /**
     * Create a new job instance.
     */
    public function __construct($symbols = null, $expiry = null)
    {
        $this->symbols = $symbols ?? ['NIFTY', 'NIFTY BANK', 'NIFTY IT', 'FINNIFTY', 'MIDCPNIFTY'];
        $this->expiry = $expiry ?? '20250911'; // Default to nearest Thursday
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('UpdateOptionChainJob: Starting option chain update for symbols: ' . implode(', ', $this->symbols));
            
            $optionsService = new OptionsService();
            $updatedCount = 0;
            
            foreach ($this->symbols as $symbol) {
                try {
                    // Get option chain data for each symbol
                    $result = $optionsService->getOptionChain($symbol, $this->expiry, [
                        'force_source' => null, // Let it use the default fallback logic
                        'forward_headers' => []
                    ]);
                    
                    if ($result['success']) {
                        $updatedCount++;
                        Log::info("UpdateOptionChainJob: Updated option chain for {$symbol} - " . count($result['data']) . " contracts");
                    } else {
                        Log::warning("UpdateOptionChainJob: Failed to update option chain for {$symbol}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    Log::error("UpdateOptionChainJob: Error updating {$symbol}: " . $e->getMessage());
                }
            }
            
            Log::info("UpdateOptionChainJob: Completed - Updated {$updatedCount}/" . count($this->symbols) . " symbols");
            
        } catch (\Exception $e) {
            Log::error('UpdateOptionChainJob: Fatal error - ' . $e->getMessage());
        }
    }
}
