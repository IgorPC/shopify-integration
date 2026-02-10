<?php

namespace App\Http\Services;

use App\Http\DTOs\ProductDTO;
use App\Http\DTOs\Responses\ShopifyProductListResponseDTO;
use App\Http\Support\ShopifyQueries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.shopify.url');
        $this->token = config('services.shopify.token');
    }

    private function query(string $query, array | null $variables): array
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->token,
            'Content-Type' => 'application/json',
        ])->post("https://{$this->baseUrl}/admin/api/2026-01/graphql.json", [
            'query' => $query,
            'variables' => $variables
        ]);

        return $response->json();
    }

    private function executeVariantUpdate($productId, $variantId, $price): void {
        $variables = [
            'productId' => $productId,
            'variants' => [
                [
                    'id' => $variantId,
                    'price' => (string) $price
                ]
            ]
        ];

        $this->query(ShopifyQueries::VARIANT_UPDATE, $variables);
    }

    private function getFirstLocation(): string | null {
        $response = $this->query(ShopifyQueries::FIRST_LOCATION, null);

        return $response['data']['locations']['edges'][0]['node']['id']
            ?? null;
    }

    private function executeProductUpdate($id, $title, $descriptionHtml): void {
        $this->query(ShopifyQueries::PRODUCT_UPDATE, ['input' => [
            'id' => $id,
            'title' => $title,
            'descriptionHtml' => $descriptionHtml
        ]]);
    }

    public function createProduct(string $title, float $price, string $descriptionHtml, int $quantity): ProductDTO | null
    {
        $locationId = $this->getFirstLocation();

        $variables = [
            'input' => [
                'title' => $title,
                'descriptionHtml' => "$descriptionHtml",
                'productOptions' => [
                    [
                        'name' => 'Title',
                        'values' => [
                            ['name' => 'Default Title']
                        ]
                    ]
                ],
                'variants' => [
                    [
                        'price' => (string) $price,
                        'optionValues' => [
                            [
                                'optionName' => 'Title',
                                'name' => 'Default Title'
                            ]
                        ],
                        'inventoryItem' => [
                            'tracked' => true
                        ],
                        'inventoryQuantities' => [
                            [
                                'locationId' => $locationId,
                                'name' => 'available',
                                'quantity' => $quantity
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->query(ShopifyQueries::PRODUCT_CREATE, $variables);

        if (isset($response['data']['productSet']['product']['id']) && $response['data']['productSet']['product']['id']) {
            return $this->getProductById($response['data']['productSet']['product']['id']);
        }

        return null;
    }

    public function updateProductById($id, $title, $description, $price): ProductDTO | null
    {
        $product = $this->getProductById($id);

        if (! $product) {
            return null;
        }

        if (($title && $product->title !== $title) || $product->description !== $description) {
            $this->executeProductUpdate($product->id, $title, $description);
        }

        if ($product->price !== $price) {
            $this->executeVariantUpdate($product->id, $product->variantId, $price);
        }

        return $this->getProductById($id);
    }

    public function getProducts($limit = 10, string $cursor = null): ShopifyProductListResponseDTO | null
    {
        try {
            $variables = [
                'first' => $limit,
                'filter' => 'status:active',
                'after' => $cursor
            ];

            $response = $this->query(ShopifyQueries::GET_PRODUCTS, $variables);

            if (isset($response['errors'])) {
                Log::error('Shopify GraphQL Errors', $response['errors']);
                throw new \Exception("Error while running shopify query integration");
            }

            $products = $response['data']['products']['edges'] ?? [];

            if (!count($products)) {
                return new ShopifyProductListResponseDTO();
            }

            return new ShopifyProductListResponseDTO(
                array_map(fn($product) => ProductDTO::fromShopify($product['node']), $products),
                $response['data']['products']['pageInfo']['hasNextPage'],
                $response['data']['products']['pageInfo']['endCursor']
            );
        } catch (\Exception $exception) {
            Log::error("Fail to load products from shopify", [
                'message' => $exception->getMessage(),
                'cursor' => $cursor,
                'limit' => $limit
            ]);

            return null;
        }
    }

    public function getProductById(string $productId): ProductDTO | null
    {
        try {
            $response = $this->query(ShopifyQueries::GET_PRODUCT, ['id' => $productId]);

            if (isset($response['errors']) || empty($response['data']['product'])) {
                return null;
            }

            return ProductDTO::fromShopify($response['data']['product']);
        } catch (\Exception $exception) {
            Log::error("Fail to get product $productId: " . $exception->getMessage());

            return null;
        }
    }

    public function deleteProductById(string $productId): bool
    {
        $variables = [
            'input' => [
                'id' => $productId
            ]
        ];

        $response = $this->query(ShopifyQueries::PRODUCT_DELETE, $variables);

        if (!empty($response['data']['productDelete']['userErrors'])) {
            Log::error("Shopify Delete Error for product $productId", $response['data']['productDelete']['userErrors']);
            return false;
        }

        return true;
    }
}
