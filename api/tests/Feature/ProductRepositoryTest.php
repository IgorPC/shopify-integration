<?php

namespace Tests\Feature\Repositories;

use App\Http\DTOs\ProductDTO;
use App\Http\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository(new Product());
    }

    /**
     * Test syncProductFromShopify: Creates a product.
     */
    public function test_it_creates_new_product_on_sync(): void
    {
        $dto = new ProductDTO(
            'gid://shopify/Product/1',
            'Test Product',
            'Description Test',
            99.90,
            10,
            'gid://v1',
            'gid://i1'
        );

        $this->repository->syncProductFromShopify($dto);

        $this->assertDatabaseHas('products', [
            'shopify_id' => 'gid://shopify/Product/1',
            'title' => 'Test Product'
        ]);
    }

    /**
     * Test syncProductFromShopify update: Updates an existing product.
     */
    public function test_it_updates_existing_product_on_sync(): void
    {
        Product::create([
            'shopify_id' => 'gid://shopify/Product/1',
            'title' => 'Old Title',
            'description' => 'Old Description',
            'price' => 10.0,
            'inventory_quantity' => 1,
            'variant_id' => 'v1',
            'inventory_item_id' => 'i1',
        ]);

        $dto = new ProductDTO(
            'gid://shopify/Product/1',
            'New Title',
            'New Description',
            20.0,
            5,
            'v1',
            'i1'
        );

        $this->repository->syncProductFromShopify($dto);

        $this->assertDatabaseHas('products', [
            'shopify_id' => 'gid://shopify/Product/1',
            'title' => 'New Title'
        ]);
    }

    /**
     * Test getAllMissingProducts: Finds local products not present in Shopify list.
     */
    public function test_it_finds_missing_products(): void
    {
        Product::create(['shopify_id' => 'id-1', 'title' => 'P1', 'description' => 'D1', 'price' => 1, 'inventory_quantity' => 1,  'variant_id' => 'variant_id_test',
            'inventory_item_id' => 'inventory_item_id_test',]);
        Product::create(['shopify_id' => 'id-2', 'title' => 'P2', 'description' => 'D2', 'price' => 1, 'inventory_quantity' => 1,  'variant_id' => 'variant_id_test',
            'inventory_item_id' => 'inventory_item_id_test',]);

        $missing = $this->repository->getAllMissingProducts(['id-1']);

        $this->assertCount(1, $missing);
        $this->assertEquals('id-2', $missing->first()->shopify_id);
    }

    /**
     * Test productExists and deleteProductById.
     */
    public function test_it_can_check_existence_and_delete_product(): void
    {
        $id = 'gid://shopify/Product/123';
        Product::create(['shopify_id' => $id, 'title' => 'T', 'description' => 'D', 'price' => 1, 'inventory_quantity' => 1,  'variant_id' => 'variant_id_test',
            'inventory_item_id' => 'inventory_item_id_test',]);

        $this->assertTrue((bool) $this->repository->productExists($id));

        $this->repository->deleteProductById($id);

        $this->assertDatabaseMissing('products', ['shopify_id' => $id]);
    }

    /**
     * Test getProductsWithPagination.
     */
    public function test_it_returns_paginated_products(): void
    {
        Product::create([
            'shopify_id' => '1',
            'title' => 'A',
            'description' => 'D',
            'price' => 1,
            'inventory_quantity' => 1,
            'variant_id' => 'v1',
            'inventory_item_id' => 'i1',
            'created_at' => now()->subMinutes(10),
        ]);

        Product::create([
            'shopify_id' => '2',
            'title' => 'B',
            'description' => 'D',
            'price' => 1,
            'inventory_quantity' => 1,
            'variant_id' => 'v2',
            'inventory_item_id' => 'i2',
            'created_at' => now(),
        ]);

        $paginated = $this->repository->getProductsWithPagination(1, 1);

        $this->assertCount(1, $paginated->items());
        $this->assertEquals(1, $paginated->items()[0]->shopify_id);
        $this->assertEquals(2, $paginated->total());
    }
}
