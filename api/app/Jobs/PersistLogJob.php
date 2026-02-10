<?php

namespace App\Jobs;

use App\Http\DTOs\PersistLogDTO;
use App\Http\Services\SystemLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PersistLogJob implements ShouldQueue
{
    use Queueable;

    private PersistLogDTO $persistLogDTO;

    /**
     * Create a new job instance.
     */
    public function __construct(PersistLogDTO $persistLogDTO)
    {
        $this->persistLogDTO = $persistLogDTO;
    }

    /**
     * Execute the job.
     */
    public function handle(SystemLogService $systemLogService): void
    {
        try {
            $systemLogService->persist($this->persistLogDTO);
        } catch (\Exception $e) {
            Log::error("Failed to persist log via Job: " . $e->getMessage());
        }
    }
}
