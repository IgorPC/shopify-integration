<?php

namespace App\Http\DTOs\Responses;

class PaginatedProductListResponseDTO
{
    protected array $products;
    protected int $currentPage;
    protected int $totalPages;
    protected int $perPage;
    protected int $totalProducts;
    protected bool $hasNextPage;
    protected bool $hasPreviousPage;

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
