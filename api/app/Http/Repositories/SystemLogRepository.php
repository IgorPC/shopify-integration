<?php

namespace App\Http\Repositories;

use App\Http\DTOs\PersistLogDTO;
use App\Models\SystemLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function getSystemLogPaginated(int $perPage = 10, int $currentPage = 1): LengthAwarePaginator
    {
        return $this->systemLog
            ->select([
                'action',
                'type',
                'target',
                'payload',
                'status',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $currentPage,
            );
    }
}
