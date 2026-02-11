import { gql } from 'graphql-tag';

export const GET_PRODUCTS = gql`
    query GetProducts($perPage: Int, $page: Int) {
        allProducts(perPage: $perPage, page: $page) {
            items {
                id
                title
                price
                inventoryQuantity
            }
            currentPage
            totalPages
            total
            hasNextPage
            hasPreviousPage
        }
    }
`;

export const GET_PRODUCT_BY_ID = gql`
    query GetProduct($id: String!) {
       product(id: $id) {
            id
            title
            price
            description
            inventoryQuantity
       }  
    }
`;

export const CREATE_PRODUCT = gql`
    mutation CreateProduct($title: String!, $price: Float!, $description: String!, $quantity: Int!) {
        createProduct(input: {
            title: $title,
            price: $price,
            description: $description,
            quantity: $quantity
        }) {
            message
            product {
                id
                title
                price
            }
        }
    }
`;

export const UPDATE_PRODUCT = gql`
    mutation UpdateProduct($id: String!, $title: String!, $price: Float!, $description: String!) {
          updateProduct(input: {
                id: $id,
                title: $title,
                price: $price,
                description: $description,
          }) {
                message
                product {
                      id
                      title
                      price
                }
          }
    }
`;

export const DELETE_PRODUCT = gql`
    mutation DeleteProduct($id: String!) {
          deleteProduct(id: $id) {
                message
                status
          }
    }
`;

export const SYNC_PRODUCT_BY_ID = gql`
    query SyncProduct($id: String!) {
           syncOne(id: $id) {
                message
                syncCount
          }
    }
`;

export const SYNC_ALL_PRODUCTS = gql`
  query SyncAll {
        syncAll {
            message
            syncCount
            missingProducts {
                id
                title
            }
        }
  }
`;

export const BULK_SYNC_TO_SHOPIFY = gql`
  mutation BulkSyncToShopify($productIds: [String!]!) {
        bulkSyncToShopify(productIds: $productIds) {
            message
            syncCount
            failedIds
        }
  }
`;