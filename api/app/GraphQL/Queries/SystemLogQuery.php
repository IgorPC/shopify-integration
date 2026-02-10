<?php

namespace app\GraphQL\Queries;

use App\Http\Services\SystemLogService;

class SystemLogQuery
{
    protected SystemLogService $systemLogService;

    public function __construct(SystemLogService $systemLogService) {
        $this->systemLogService = $systemLogService;
    }

    public function all($_, array $args)
    {
        return $this->systemLogService->getSystemLogPaginated(
            $args['perPage'] ?? 10,
            $args['page'] ?? 1
        );
    }
}
