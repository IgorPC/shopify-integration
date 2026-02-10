<?php

namespace App\Http\Services;

use App\Http\DTOs\PersistLogDTO;
use App\Http\DTOs\Responses\PaginatedResponseDTO;
use App\Http\DTOs\SystemLogDTO;
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

    public function getSystemLogPaginated(int $perPage = 10, int $currentPage = 1)
    {
        $logs = $this->repository->getSystemLogPaginated($perPage, $currentPage);

        return new PaginatedResponseDTO(
            $logs->getCollection()->transform(function ($log) {
                return SystemLogDTO::fromModel($log);
            })->toArray(),
            $logs->currentPage(),
            $logs->lastPage(),
            $logs->total(),
            $perPage
        );
    }
}
