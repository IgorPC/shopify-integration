<?php

namespace Tests\Unit;

use App\Http\DTOs\Responses\PaginatedProductListResponseDTO;
use App\Http\DTOs\Responses\ShopifyProductListResponseDTO;
use App\Http\DTOs\Responses\SyncProductResponseDTO;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use App\Http\Services\ProductService;
use App\Http\Services\ShopifyService;
use App\Http\Services\SystemLogService;
use App\Http\Repositories\ProductRepository;
use App\Http\DTOs\ProductDTO;
use App\Jobs\PersistLogJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Mockery;

class ProductServiceTest extends TestCase
{
    private ProductRepository $productRepository;
    private ShopifyService $shopifyService;
    private SystemLogService $systemLogService;
    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking dependencies
        $this->productRepository = Mockery::mock(ProductRepository::class);
        $this->shopifyService = Mockery::mock(ShopifyService::class);
        $this->systemLogService = Mockery::mock(SystemLogService::class);

        $this->productService = new ProductService(
            $this->productRepository,
            $this->shopifyService,
            $this->systemLogService
        );

        Queue::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test if getAllProductsPaginated returns a paginated DTO response correctly.
     */
    public function test_get_all_products_paginated_returns_dto()
    {
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);
        $mockPaginator->shouldReceive('getCollection')->andReturn(collect([]));
        $mockPaginator->shouldReceive('currentPage')->andReturn(1);
        $mockPaginator->shouldReceive('lastPage')->andReturn(1);
        $mockPaginator->shouldReceive('total')->andReturn(0);

        $this->productRepository->shouldReceive('getProductsWithPagination')
            ->once()
            ->with(10, 1)
            ->andReturn($mockPaginator);

        $response = $this->productService->getAllProductsPaginated(10, 1);

        $this->assertInstanceOf(PaginatedProductListResponseDTO::class, $response);
    }

    /**
     * Test createProduct validation: should throw exception if title is too short.
     */
    public function test_create_product_throws_exception_for_short_title()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Error while creating product Abc");

        $this->productService->createProduct('Abc', 100.0, 'Description', 10);
    }

    /**
     * Test createProduct success: calls Shopify, syncs locally and dispatches log job.
     */
    public function test_create_product_success()
    {
        $mockProduct = new ProductDTO('gid://123', 'Valid Title', 'Description', 100.0, 10);

        $this->shopifyService->shouldReceive('createProduct')->once()->andReturn($mockProduct);
        $this->shopifyService->shouldReceive('getProductById')->andReturn($mockProduct);
        $this->productRepository->shouldReceive('syncProductFromShopify')->once();

        $response = $this->productService->createProduct('Valid Title', 100.0, 'Description', 10);

        $this->assertEquals("Product successfully created", $response->message);
        Queue::assertPushed(PersistLogJob::class);
    }

    /**
     * Test deleteProductById: checks Shopify existence, deletes from both and logs.
     */
    public function test_delete_product_by_id_success()
    {
        $productId = 'gid://123';
        $mockProduct = new ProductDTO($productId, 'Title', 'Desc', 10.0, 1);

        $this->shopifyService->shouldReceive('getProductById')->with($productId)->andReturn($mockProduct);
        $this->shopifyService->shouldReceive('deleteProductById')->with($productId)->andReturn(true);
        $this->productRepository->shouldReceive('productExists')->with($productId)->andReturn(true);
        $this->productRepository->shouldReceive('deleteProductById')->with($productId)->once();

        $result = $this->productService->deleteProductById($productId);

        $this->assertTrue($result);
        Queue::assertPushed(PersistLogJob::class);
    }

    /**
     * Test updateProductById: ensures title validation and Shopify update call.
     */
    public function test_update_product_by_id_validation_error()
    {
        $productId = 'gid://123';

        $mockProduct = new ProductDTO(
            $productId,
            'Original Title',
            'Original Desc',
            10.0,
            1
        );

        $this->shopifyService->shouldReceive('getProductById')->andReturn($mockProduct);
        $this->expectException(\Exception::class);
        $this->productService->updateProductById($productId, 'Short', 'Desc', 50.0);
    }

    /**
     * Test syncLocalProductToShopify: uses DB transaction, creates on Shopify and deletes old local record.
     */
    public function test_sync_local_product_to_shopify_uses_transaction()
    {
        $localId = 'local-123';
        $shopifyId = 'gid://shopify/Product/999';

        $mockLocalProduct = new Product();
        $mockLocalProduct->title = 'Local Title';
        $mockLocalProduct->price = 50.0;
        $mockLocalProduct->description = 'Desc';
        $mockLocalProduct->inventory_quantity = 5;

        $mockShopifyProduct = new ProductDTO(
            $shopifyId,
            'Local Title',
            'Desc',
            50.0,
            5
        );

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->productRepository->shouldReceive('getProductById')
            ->with($localId)
            ->andReturn($mockLocalProduct);
        $this->shopifyService->shouldReceive('getProductById')
            ->with($localId)
            ->andReturn(null);
        $this->shopifyService->shouldReceive('createProduct')
            ->once()
            ->andReturn($mockShopifyProduct);
        $this->shopifyService->shouldReceive('getProductById')
            ->with($shopifyId)
            ->andReturn($mockShopifyProduct);
        $this->productRepository->shouldReceive('syncProductFromShopify')
            ->once()
            ->with($mockShopifyProduct);
        $this->productRepository->shouldReceive('deleteProductById')
            ->once()
            ->with($localId);
        $this->productService->syncLocalProductToShopify($localId);

        Queue::assertPushed(PersistLogJob::class);
    }

    /**
     * Test syncAll: iterates through Shopify pages and returns missing local products.
     */
    public function test_sync_all_completes_full_cycle()
    {
        $shopifyItem = new ProductDTO('gid://1', 'Title', 'Desc', 1.0, 1);

        $mockResponse = new ShopifyProductListResponseDTO(
            [$shopifyItem],
            false,
            null
        );

        $this->shopifyService->shouldReceive('getProducts')->once()->andReturn($mockResponse);
        $this->productRepository->shouldReceive('syncProductFromShopify')->once();
        $this->productRepository->shouldReceive('getAllMissingProducts')->andReturn(collect([]));

        $response = $this->productService->syncAll();

        $this->assertInstanceOf(SyncProductResponseDTO::class, $response);
        $this->assertEquals(1, $response->syncCount);
        Queue::assertPushed(PersistLogJob::class);
    }
}
