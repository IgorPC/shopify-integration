<?php

namespace App\Http\Services;

use App\Http\DTOs\PersistLogDTO;
use App\Http\Enums\SystemLogEnum;
use App\Http\Repositories\SystemLogRepository;

class SystemLogService
{
    private SystemLogRepository $repository;

    public function __construct(SystemLogRepository $repository) {
        $this->repository = $repository;
    }

    public function persist(PersistLogDTO $persistLogDTO): void
    {
        $this->repository->persistLog($persistLogDTO);
    }
}
