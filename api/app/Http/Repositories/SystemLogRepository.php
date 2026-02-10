<?php

namespace App\Http\Repositories;

use App\Http\DTOs\PersistLogDTO;
use App\Models\SystemLog;

class SystemLogRepository
{
    private SystemLog $systemLog;

    public function __construct(SystemLog $systemLog) {
        $this->systemLog = $systemLog;
    }

    public function persistLog(PersistLogDTO $persistLogDTO)
    {
        $this->systemLog->create([
            'action' => $persistLogDTO->action,
            'type' => $persistLogDTO->type,
            'target' => $persistLogDTO->target,
            'payload' => $persistLogDTO->payload,
            'status' => $persistLogDTO->status,
        ]);
    }
}
