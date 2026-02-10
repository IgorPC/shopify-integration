<?php

namespace app\GraphQL\Queries;

use App\Http\Services\ProductService;
use GraphQL\Error\Error;

class ProductQuery
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function all($_, array $args)
    {
        return $this->productService->getAllProductsPaginated(
            $args['perPage'] ?? 10,
            $args['page'] ?? 1
        );
    }

    public function find($_, array $args)
    {
        try {
            return $this->productService->getProductById($args['id']);
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }
    }

    public function syncAll()
    {
        try {
            return $this->productService->syncAll();
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }

    }

    public function syncOne($_, array $args)
    {
        try {
            return $this->productService->syncProductById($args['id']);
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }
    }

    public function syncLocalProductToShopify($_, array $args)
    {
        try {
            return $this->productService->syncLocalProductToShopify($args['id']);
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }
    }
}
