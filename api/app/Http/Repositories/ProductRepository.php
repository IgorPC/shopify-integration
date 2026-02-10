<?php

namespace App\Http\Repositories;

use App\Http\DTOs\ProductDTO;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository
{
    private Product $product;

    public function __construct(Product $product) {
        $this->product = $product;
    }

    public function syncProductFromShopify(ProductDTO $product): void
    {
        $this->product->updateOrCreate(
            [
                'shopify_id' => $product->id
            ],
            [
                'title' => $product->title,
                'description' => $product->description,
                'price' => $product->price,
                'inventory_quantity' => $product->inventoryQuantity,
                'variant_id' => $product->variantId,
                'inventory_item_id' => $product->inventoryItemId,
            ]
        );
    }

    public function getAllMissingProducts(array $productIds): Collection
    {
        return $this->product->whereNotIn('shopify_id', $productIds)->get();
    }

    public function productExists(string $productId): bool
    {
        return $this->product->where(['shopify_id' => $productId])->count();
    }

    public function deleteProductById(string $productId): void
    {
        $this->product->where(['shopify_id' => $productId])->delete();
    }

    public function getProductById(string $productId): Product | null
    {
        return $this->product->where(['shopify_id' => $productId])->first();
    }

    public function getProductsWithPagination(int $perPage = 10, int $currentPage = 1): LengthAwarePaginator
    {
        return $this->product
            ->select([
                'shopify_id',
                'title',
                'description',
                'price',
                'inventory_quantity'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $currentPage,
            );
    }
}
