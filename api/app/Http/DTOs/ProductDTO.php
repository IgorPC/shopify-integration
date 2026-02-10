<?php

namespace App\Http\DTOs;

use App\Models\Product;

class ProductDTO
{
    public string $id;
    public string $title;
    public string $description;
    public float $price;
    public int $inventoryQuantity;
    public string | null $variantId;
    public string | null $inventoryItemId;

    public function __construct(
        string $id,
        string $title,
        string $description,
        float $price,
        int $inventoryQuantity,
        string | null $variantId = null,
        string | null $inventoryItemId = null
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->inventoryQuantity = $inventoryQuantity;
        $this->variantId = $variantId;
        $this->inventoryItemId = $inventoryItemId;
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            $product->shopify_id,
            $product->title,
            $product->description,
            $product->price,
            $product->inventory_quantity,
            null,
            null,
        );
    }

    public static function fromShopify(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['descriptionHtml'],
            $data['variants']['edges'][0]['node']['price'],
            $data['variants']['edges'][0]['node']['inventoryQuantity'],
            $data['variants']['edges'][0]['node']['id'] ?? null,
            $data['variants']['edges'][0]['node']['inventoryItem']['id'] ?? null,
        );
    }

    public function toString(): string
    {
        return json_encode([
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'inventory_quantity' => $this->inventoryQuantity
        ]);
    }
}
