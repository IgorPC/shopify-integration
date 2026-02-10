<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\ShopifyService;
use App\Http\DTOs\ProductDTO;
use App\Http\DTOs\Responses\ShopifyProductListResponseDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class ShopifyServiceTest extends TestCase
{
    private ShopifyService $shopifyService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.shopify.url', 'test-shop.myshopify.com');
        Config::set('services.shopify.token', 'shpat_test_token');

        $this->shopifyService = new ShopifyService();
    }

    /**
     * Test getProductById: Mocks a GraphQL response and verifies if the ProductDTO is correctly hydrated.
     */
    public function test_get_product_by_id_returns_product_dto()
    {
        $productId = 'gid://shopify/Product/12345';

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'product' => [
                        'id' => $productId,
                        'title' => 'Test Shopify Product',
                        'descriptionHtml' => 'Description',
                        'variants' => [
                            'edges' => [
                                [
                                    'node' => [
                                        'id' => 'gid://v1',
                                        'price' => '100.00',
                                        'inventoryItem' => ['id' => 'gid://i1'],
                                        'inventoryQuantity' => 10
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->shopifyService->getProductById($productId);

        $this->assertInstanceOf(ProductDTO::class, $result);
        $this->assertEquals($productId, $result->id);
        $this->assertEquals('Test Shopify Product', $result->title);
    }

    /**
     * Test getProducts: Verifies the transformation of a Shopify connection (edges/nodes) into a paginated DTO.
     */
    public function test_get_products_returns_paginated_dto()
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'products' => [
                        'edges' => [
                            ['node' => ['id' => 'gid://p1', 'title' => 'P1', 'descriptionHtml' => 'D', 'variants' => ['edges' => [['node' => ['id' => 'v1', 'price' => '10.0', 'inventoryItem' => ['id' => 'i1'], 'inventoryQuantity' => 5]]]]]]
                        ],
                        'pageInfo' => [
                            'hasNextPage' => true,
                            'endCursor' => 'cursor-123'
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->shopifyService->getProducts(1);

        $this->assertInstanceOf(ShopifyProductListResponseDTO::class, $result);
        $this->assertCount(1, $result->items);
        $this->assertTrue($result->hasNextPage);
        $this->assertEquals('cursor-123', $result->cursor);
    }

    /**
     * Test createProduct: Simulates the flow of getting a location ID first, then creating a product.
     */
    public function test_create_product_success()
    {
        Http::fake([
            '*' => Http::sequence()
                ->push(['data' => ['locations' => ['edges' => [['node' => ['id' => 'gid://loc1']]]]]], 200) // For getFirstLocation
                ->push(['data' => ['productSet' => ['product' => ['id' => 'gid://p-new']]]], 200)       // For createProduct
                ->push(['data' => ['product' => ['id' => 'gid://p-new', 'title' => 'New', 'descriptionHtml' => 'D', 'variants' => ['edges' => [['node' => ['id' => 'v1', 'price' => '20.0', 'inventoryItem' => ['id' => 'i1'], 'inventoryQuantity' => 5]]]]]]], 200) // For final getProductById
        ]);

        $result = $this->shopifyService->createProduct('New', 20.0, 'Desc', 5);

        $this->assertInstanceOf(ProductDTO::class, $result);
        $this->assertEquals('gid://p-new', $result->id);
        Http::assertSentCount(3);
    }

    /**
     * Test deleteProductById: Verifies if userErrors in Shopify response results in a false return.
     */
    public function test_delete_product_returns_false_on_user_errors()
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'productDelete' => [
                        'userErrors' => [
                            ['message' => 'Product not found']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->shopifyService->deleteProductById('gid://invalid');

        $this->assertFalse($result);
    }

    /**
     * Test updateProductById: Ensures no unnecessary requests are made if data hasn't changed.
     */
    public function test_update_product_does_nothing_if_data_is_equal()
    {
        $id = 'gid://p1';
        $mockData = [
            'id' => $id,
            'title' => 'Same Title',
            'descriptionHtml' => 'Same Desc',
            'variants' => ['edges' => [['node' => ['id' => 'v1', 'price' => '10.0', 'inventoryItem' => ['id' => 'i1'], 'inventoryQuantity' => 5]]]]
        ];

        Http::fake([
            '*' => Http::response(['data' => ['product' => $mockData]], 200)
        ]);

        $this->shopifyService->updateProductById($id, 'Same Title', 'Same Desc', 10.0);

        Http::assertSent(function ($request) {
            return !str_contains($request['query'], 'productUpdate') &&
                !str_contains($request['query'], 'productVariantUpdate');
        });
    }
}
