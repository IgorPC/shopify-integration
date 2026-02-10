<?php

namespace App\Http\DTOs\Responses;

class SyncProductResponseDTO
{
    public string $message;
    public int $syncCount;
    public array | null $missingProducts;

    public function __construct(string $message, int $syncCount, array | null $missingProducts = null) {
        $this->message = $message;
        $this->syncCount = $syncCount;
        $this->missingProducts = $missingProducts;
    }
}
