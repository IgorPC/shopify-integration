<?php

namespace App\Http\DTOs\Responses;

use App\Http\DTOs\ProductDTO;

class CreateOrUpdateProductResponseDTO
{
    public string $message;
    public ProductDTO | null $product;

    public function __construct(string $message, ProductDTO | null $product) {
        $this->message = $message;
        $this->product = $product;
    }
}
