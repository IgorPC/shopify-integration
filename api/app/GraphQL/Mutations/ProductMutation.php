<?php

namespace App\GraphQL\Mutations;

use App\Http\Services\ProductService;
use GraphQL\Error\Error;

class ProductMutation
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function bulkSync($_, array $args)
    {
        return $this->productService->bulkSyncLocalProductToShopify(
            $args['productIds']
        );
    }

    public function deleteProduct($_, array $args)
    {
        $success = $this->productService->deleteProductById($args['id']);

        return [
            'status' => $success,
            'message' => $success ? "Product successfully deleted." : "Error while deleting the product."
        ];
    }

    public function deleteLocalProduct($_, array $args)
    {
        $success = $this->productService->deleteProductFromDatabase($args['id']);

        return [
            'status' => $success,
            'message' => $success ? "Product successfully deleted." : "Error while deleting the product."
        ];
    }

    public function create($_, array $args)
    {
        try {
            return $this->productService->createProduct(
                $args['title'],
                $args['price'],
                $args['description'],
                $args['quantity']
            );
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }
    }

    public function update($_, array $args)
    {
        try {
            return $this->productService->updateProductById(
                $args['id'],
                $args['title'],
                $args['description'],
                $args['price'],
            );
        } catch (\Exception $exception) {
            return new Error($exception->getMessage());
        }
    }
}
