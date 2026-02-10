<?php

namespace App\Http\DTOs\Responses;

class PaginatedResponseDTO
{
    public array $items;
    public int $currentPage;
    public int $totalPages;
    public int $perPage;
    public int $total;
    public bool $hasNextPage;
    public bool $hasPreviousPage;

    public function __construct(array $items, int $currentPage, int $totalPages, int $total, int $perPage) {
        $this->items = $items;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->hasNextPage = $this->currentPage < $this->totalPages;
        $this->hasPreviousPage = $this->currentPage > 1;
    }

}
