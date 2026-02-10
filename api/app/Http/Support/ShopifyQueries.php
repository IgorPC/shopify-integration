<?php

namespace App\Http\Support;

class ShopifyQueries
{
    public const GET_PRODUCT = <<<'GRAPHQL'
        query GetProduct($id: ID!) {
            product(id: $id) {
                id
                title
                status
                descriptionHtml
                variants(first: 1) {
                    edges {
                        node {
                            id
                            price
                            inventoryQuantity
                            inventoryItem {
                                id
                            }
                        }
                    }
                    }
                }
            }
        GRAPHQL;

    public const GET_PRODUCTS = <<<'GRAPHQL'
        query GetActiveProducts($first: Int!, $filter: String!, $after: String) {
            products(first: $first, query: $filter, after: $after) {
                edges {
                    node {
                        id
                        title
                        status
                        descriptionHtml
                        variants(first: 1) {
                            edges {
                                node {
                                    id
                                    price
                                    inventoryQuantity
                                    inventoryItem {
                                        id
                                    }
                                }
                            }
                        }
                    }
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
              }
            }
        GRAPHQL;

    public const VARIANT_UPDATE = <<<'GRAPHQL'
        mutation updateVariant($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
                productVariantsBulkUpdate(productId: $productId, variants: $variants) {
                    productVariants {
                        id
                        price
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

    public const PRODUCT_UPDATE = <<<'GRAPHQL'
        mutation productUpdate($input: ProductInput!) {
            productUpdate(input: $input) {
                userErrors { message }
                }
            }
        GRAPHQL;

    public const FIRST_LOCATION = <<<'GRAPHQL'
        query {
            locations(first: 1) {
                edges {
                    node {
                        id
                    }
                }
            }
        }
    GRAPHQL;

    public const PRODUCT_CREATE = <<<'GRAPHQL'
        mutation productSet($input: ProductSetInput!) {
            productSet(input: $input) {
                product {
                    id
                    title
                    variants(first: 1) {
                        edges {
                            node {
                                id
                                price
                                inventoryItem {
                                    id
                                }
                            }
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }
    GRAPHQL;

    public const PRODUCT_DELETE = <<<'GRAPHQL'
        mutation productDelete($input: ProductDeleteInput!) {
            productDelete(input: $input) {
                deletedProductId
                userErrors {
                    field
                    message
                }
            }
        }
    GRAPHQL;
}
