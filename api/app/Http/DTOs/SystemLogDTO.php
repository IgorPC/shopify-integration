<?php

namespace App\Http\DTOs;

use App\Models\SystemLog;

class SystemLogDTO
{
    public string $action;
    public string $type;
    public string | null $target;
    public string $payload;
    public bool $status;
    public string $created_at;

    public function __construct(
        string $action,
        string $type,
        string | null $target,
        string $payload,
        bool $status,
        string $created_at
    )
    {
        $this->action = $action;
        $this->type = $type;
        $this->target = $target;
        $this->payload = $payload;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public static function fromModel(SystemLog $systemLog): self
    {
        return new self(
            $systemLog->action,
            $systemLog->type,
            $systemLog->target,
            $systemLog->payload,
            $systemLog->status,
            $systemLog->created_at
        );
    }
}
