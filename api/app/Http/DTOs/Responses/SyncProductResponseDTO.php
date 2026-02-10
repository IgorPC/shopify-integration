<?php

namespace App\Http\DTOs\Responses;

class SyncProductResponseDTO
{
    protected string $message;
    protected int $syncCount;
    protected array | null $missingProducts;

    public function __construct(string $message, int $syncCount, array | null $missingProducts = null) {
        $this->message = $message;
        $this->syncCount = $syncCount;
        $this->missingProducts = $missingProducts;
    }
}
