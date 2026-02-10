<?php

namespace App\Http\DTOs;

use App\Http\Enums\LogTypeEnum;
use App\Http\Enums\LogActionEnum;

class PersistLogDTO
{
    public LogActionEnum $action;
    public LogTypeEnum $type;
    public string | null $target;
    public string $payload;
    public bool $status;

    public function __construct(
        LogActionEnum $action,
        LogTypeEnum $type,
        string | null $target,
        string $payload,
        bool $status = true
    )
    {
        $this->action = $action;
        $this->type = $type;
        $this->target = $target;
        $this->payload = $payload;
        $this->status = $status;
    }
}
