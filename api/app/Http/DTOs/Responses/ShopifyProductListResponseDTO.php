<?php

namespace App\Http\DTOs\Responses;

class ShopifyProductListResponseDTO
{
    public array $items;
    public bool $hasNextPage;
    public string | null $cursor;

    public function __construct(
        array $items = [],
        bool $hasNextPage = false,
        string | null $cursor = null
    )
    {
        $this->items = $items;
        $this->hasNextPage = $hasNextPage;
        $this->cursor = $cursor;
    }
}
