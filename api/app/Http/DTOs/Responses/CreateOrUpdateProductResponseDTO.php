<?php

namespace App\Http\DTOs\Responses;

use App\Http\DTOs\ProductDTO;

class CreateOrUpdateProductResponseDTO
{
    protected string $message;
    protected ProductDTO | null $product;

    public function __construct(string $message, ProductDTO | null $product) {
        $this->message = $message;
        $this->product = $product;
    }
}
