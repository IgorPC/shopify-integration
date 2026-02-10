<?php

namespace App\Http\DTOs\Responses;

class PaginatedProductListResponseDTO
{
    public array $products;
    public int $currentPage;
    public int $totalPages;
    public int $perPage;
    public int $totalProducts;
    public bool $hasNextPage;
    public bool $hasPreviousPage;

    public function __construct(array $products, int $currentPage, int $totalPages, int $totalProducts, int $perPage) {
        $this->products = $products;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->perPage = $perPage;
        $this->totalProducts = $totalProducts;
        $this->hasNextPage = $this->currentPage < $this->totalPages;
        $this->hasPreviousPage = $this->currentPage > 1;
    }

}
