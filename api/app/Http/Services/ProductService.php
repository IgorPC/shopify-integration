<?php

namespace App\Http\Services;

use App\Http\DTOs\PersistLogDTO;
use App\Http\DTOs\ProductDTO;
use App\Http\DTOs\Responses\CreateOrUpdateProductResponseDTO;
use App\Http\DTOs\Responses\PaginatedProductListResponseDTO;
use App\Http\DTOs\Responses\SyncProductResponseDTO;
use App\Http\Enums\LogTypeEnum;
use App\Http\Enums\LogActionEnum;
use App\Http\Repositories\ProductRepository;
use App\Jobs\PersistLogJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    private ProductRepository $productRepository;
    private ShopifyService $shopifyService;

    public function __construct(
        ProductRepository $productRepository,
        ShopifyService $shopifyService,
        SystemLogService $systemLogService
    ) {
        $this->productRepository = $productRepository;
        $this->shopifyService = $shopifyService;
    }

    public function getAllProductsPaginated(int $perPage = 10, int $currentPage = 1)
    {
        $products = $this->productRepository->getProductsWithPagination($perPage, $currentPage);

        return new PaginatedProductListResponseDTO(
            $products->getCollection()->transform(function ($product) {
                return ProductDTO::fromModel($product);
            })->toArray(),
            $products->currentPage(),
            $products->lastPage(),
            $products->total(),
            $perPage
        );
    }

    public function getProductById(string $productId): ProductDTO | null
    {
        $product = $this->productRepository->getProductById($productId);

        if (! $product) {
            throw new \Exception('Product not found');
        }

        return ProductDTO::fromModel($product);
    }

    public function createProduct(string $title, float $price, string $description, int $quantity): CreateOrUpdateProductResponseDTO
    {
        try {
            if (! $title) {
                throw new \Exception("Title is required");
            }

            if ($price < 0) {
                throw new \Exception("Price is required");
            }

            if ($quantity < 0) {
                throw new \Exception("Quantity is required");
            }

            $newProduct = $this->shopifyService->createProduct($title, $price, $description, $quantity);
            $this->syncProductById($newProduct->id);

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::CREATE,
                LogTypeEnum::PRODUCT,
                $newProduct->id,
                "Product successfully created",
            ));

            return new CreateOrUpdateProductResponseDTO("Product successfully created", $newProduct);
        } catch (\Exception $exception) {
            Log::error("Error while creating product $title", [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);
            throw new \Exception("Error while creating product $title");
        }
    }

    public function deleteProductFromDatabase(string $productId): bool
    {
        if (! $this->productRepository->productExists($productId)) {
            return false;
        }

        $this->productRepository->deleteProductById($productId);

        PersistLogJob::dispatch(new PersistLogDTO(
            LogActionEnum::DELETE,
            LogTypeEnum::PRODUCT,
            $productId,
            "Product successfully deleted",
        ));

        return true;
    }

    public function deleteProductById(string $productId): bool
    {
        try {
            $shopifyProduct = $this->shopifyService->getProductById($productId);

            if ($shopifyProduct) {
                if (! $this->shopifyService->deleteProductById($productId)) {
                    return false;
                }
            }

            if ($this->productRepository->productExists($productId)) {
                $this->productRepository->deleteProductById($productId);
            }

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::DELETE,
                LogTypeEnum::PRODUCT,
                $productId,
                "Product successfully deleted",
            ));

            return true;
        } catch (\Exception $exception) {
            Log::error("Error while deleting product $productId", [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);
            throw new \Exception("Error while deleting product $productId");
        }
    }


    public function updateProductById(string $productId, string $title, string $description, float $price): CreateOrUpdateProductResponseDTO
    {
        try {
            $shopifyProduct = $this->shopifyService->getProductById($productId);

            if (! $shopifyProduct) {
                return new CreateOrUpdateProductResponseDTO("Product does not exist in shopify", null);
            }

            if (! $title) {
                throw new \Exception("Product title is required");
            }

            if ($price < 0) {
                throw new \Exception("Product price must be greater than 0");
            }

            $updatedProduct = $this->shopifyService->updateProductById($productId, $title, $description, $price);
            $this->syncProductById($productId);

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::UPDATE,
                LogTypeEnum::PRODUCT,
                $productId,
                "Product successfully updated",
            ));

            return new CreateOrUpdateProductResponseDTO("Product successfully updated", $updatedProduct);
        } catch (\Exception $exception) {
            Log::error("Error while updating product $productId", [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);
            throw new \Exception("Error while updating product $productId");
        }
    }

    public function bulkSyncLocalProductToShopify(array $productIds): SyncProductResponseDTO
    {
        $syncedCount = 0;
        $failedIds = [];

        $limitedIds = array_slice($productIds, 0, 10);

        foreach ($limitedIds as $productId) {
            try {
                $this->syncLocalProductToShopify($productId);
                $syncedCount++;
            } catch (\Exception $exception) {
                Log::error("Bulk Sync Failure for ID $productId: " . $exception->getMessage());
                $failedIds[] = $productId;
                continue;
            }
        }

        $message = count($failedIds) > 0
            ? "Sync completed with some errors."
            : "All products synced successfully.";

        return new SyncProductResponseDTO(
            $message,
            $syncedCount,
            $failedIds
        );
    }

    public function syncLocalProductToShopify(string $productId): CreateOrUpdateProductResponseDTO
    {
        try {
            DB::beginTransaction();

            $product = $this->productRepository->getProductById($productId);

            if (! $product) {
                throw new \Exception('Product not found');
            }

            if (! $this->shopifyService->getProductById($productId)) {
                $shopifyProduct = $this->shopifyService->createProduct($product->title, $product->price, $product->description, $product->inventory_quantity);
            } else {
                $shopifyProduct = $this->shopifyService->getProductById($productId);
            }

            if (! $shopifyProduct) {
                throw new \Exception('Error while creating product');
            }

            $this->productRepository->deleteProductById($productId);
            $this->syncProductById($shopifyProduct->id);

            DB::commit();

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::SYNC,
                LogTypeEnum::PRODUCT,
                $productId,
                "Product successfully synced",
            ));

            return new CreateOrUpdateProductResponseDTO("Product successfully created", $shopifyProduct);
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::error("Error while syncing product $productId", [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);

            throw new \Exception("Error while syncing product $productId");
        }
    }

    public function syncProductById(string $productId): SyncProductResponseDTO
    {
        try {
            $shopifyProduct = $this->shopifyService->getProductById($productId);

            if (! $shopifyProduct) {
                return new SyncProductResponseDTO("Product does not exist in shopify", 0);
            }

            $this->productRepository->syncProductFromShopify($shopifyProduct);

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::SYNC,
                LogTypeEnum::PRODUCT,
                $productId,
                "Product synced successfully",
            ));

            return new SyncProductResponseDTO("Product successfully synced", 1);
        } catch (\Exception $exception) {
            Log::error("Error to sync product $productId from shopify to the DB", [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);
            throw new \Exception("Error to sync the product $productId from shopify");
        }
    }

    public function syncAll(): SyncProductResponseDTO
    {
        try {
            $productsOnShopify = [];
            $syncCount = 0;
            $hasNextPage = true;
            $cursor = null;

            while ($hasNextPage) {
                $response = $this->shopifyService->getProducts(50, $cursor);

                if (empty($response->items)) {
                    break;
                }

                foreach ($response->items as $product) {
                    $this->productRepository->syncProductFromShopify($product);
                    $syncCount++;
                    $productsOnShopify[] = $product->id;
                }

                $hasNextPage = $response->hasNextPage;
                $cursor = $response->cursor;
            }

            if (! $syncCount) {
                return new SyncProductResponseDTO("Product list is empty", $syncCount);
            }

            $missingShopifyProducts = $this->productRepository->getAllMissingProducts($productsOnShopify);
            $missingDTOs = $missingShopifyProducts->count() > 0
                ? $missingShopifyProducts->map(fn($p) => ProductDTO::fromModel($p))->toArray()
                : [];

            PersistLogJob::dispatch(new PersistLogDTO(
                LogActionEnum::SYNC,
                LogTypeEnum::SYSTEM,
                null,
                "$syncCount products synced successfully",
            ));

            return new SyncProductResponseDTO(
                $missingShopifyProducts->count() > 0
                    ? "Sync completed: Local database has items not present on Shopify."
                    : "Database is perfectly in sync with Shopify.",
                $syncCount,
                $missingDTOs
            );
        } catch (\Exception $exception) {
            Log::error('Error to sync the products from shopify to the DB', [
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ]);
            throw new \Exception("Error to sync the products from shopify");
        }
    }
}
