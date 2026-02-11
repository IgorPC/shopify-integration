import {useQuery, useMutation, useLazyQuery} from '@vue/apollo-composable';
import {
    GET_PRODUCTS,
    CREATE_PRODUCT,
    GET_PRODUCT_BY_ID,
    UPDATE_PRODUCT,
    DELETE_PRODUCT,
    SYNC_PRODUCT_BY_ID, SYNC_ALL_PRODUCTS, BULK_SYNC_TO_SHOPIFY
} from '../graphql/products';
import {computed, type Ref} from 'vue';
import type {MissingProduct, PaginatedProducts, Product, SyncAllResponse} from "../types/product.ts";

export function getAllProducts(perPage: Ref<number> | number = 10, page: Ref<number> | number = 1) {
    const { result, loading, error, refetch } = useQuery<{ allProducts: PaginatedProducts }>(
        GET_PRODUCTS,
        () => ({
            perPage: typeof perPage === 'object' ? perPage.value : perPage,
            page: typeof page === 'object' ? page.value : page
        })
    );

    const pagination = computed<PaginatedProducts>(() => {
        const data = result.value?.allProducts;

        return {
            items: data?.items ?? [],
            total: data?.total ?? 0,
            currentPage: data?.currentPage ?? 1,
            totalPages: data?.totalPages ?? 1,
            hasNextPage: data?.hasNextPage ?? false,
            hasPreviousPage: data?.hasPreviousPage ?? false
        };
    });

    return {
        pagination,
        loading,
        error,
        refetch
    };
}

export function getProduct() {
    const { load, result, loading, error, onResult } = useLazyQuery<{ product: Product }>(
        GET_PRODUCT_BY_ID,
    );

    const product = computed(() => result.value?.product ?? null);

    const fetchProduct = async (id: string) => {
        try {
            return await load(GET_PRODUCT_BY_ID, { id });
        } catch (err) {
            alert('Error fetching product');
        }
    };

    return {
        product,
        fetchProduct,
        loading,
        error,
        onResult
    };
}

export function createProduct() {
    const { mutate, loading, error } = useMutation(CREATE_PRODUCT, {
        refetchQueries: ['GetProducts']
    });

    const handleCreate = async (product: Omit<Product, 'id'>): Promise<Product> => {
        try {
            const response = await mutate({
                title: product.title,
                price: product.price,
                description: product.description,
                quantity: product.inventoryQuantity
            });

            return response?.data?.createProduct.product;
        } catch (err) {
            alert('Error creating product');
            throw err;
        }
    };

    return {
        handleCreate,
        isSaving: loading,
        createError: error
    };
}

export function updateProduct() {
    const { mutate, loading, error } = useMutation(UPDATE_PRODUCT, {
        refetchQueries: ['GetProducts']
    });

    const handleUpdate = async (product: Omit<Product, 'inventoryQuantity'>): Promise<Product> => {
        try {
            const response = await mutate({
                id: product.id,
                title: product.title,
                price: product.price,
                description: product.description,
            });

            return response?.data?.updateProduct.product;
        } catch (err) {
            alert('Error updating product');
            throw err;
        }
    };

    return {
        handleUpdate,
        isSaving: loading,
        updateError: error
    };
}

export function deleteProduct() {
    const { mutate, loading, error } = useMutation(DELETE_PRODUCT, {
        refetchQueries: ['GetProducts']
    });

    const handleDelete = async (id: string): Promise<boolean> => {
        try {
            const response = await mutate({
                id: id,
            });

            return response?.data?.deleteProduct.status;
        } catch (err) {
            alert('Error deleting product');
            throw err;
        }
    };

    return {
        handleDelete,
        isDeleting: loading,
        deleteError: error
    };
}

export function syncProduct() {
    const { load, loading, error, onResult } = useLazyQuery(SYNC_PRODUCT_BY_ID);

    const handleSync = async (id: string) => {
        try {
            await load(SYNC_PRODUCT_BY_ID, { id });
        } catch (err) {
            alert('Error syncing product');
            throw err;
        }
    };

    return {
        handleSync,
        isSyncing: loading,
        syncError: error,
        onSyncResult: onResult
    };
}

export function syncAll() {
    const { load, loading, error, refetch } = useLazyQuery<SyncAllResponse>(
        SYNC_ALL_PRODUCTS,
        {},
        {
            fetchPolicy: 'no-cache',
            nextFetchPolicy: 'network-only'
        }
    );

    const handleSyncAll = async () => {
        try {
            const loaded = await load();
            let data: MissingProduct[] = (loaded && 'syncAll' in loaded)
                ? loaded.syncAll.missingProducts
                : [];

            if (! loaded) {
                const refetched = await refetch();
                data = refetched?.data?.syncAll?.missingProducts ?? [];
            }

            return { missingProducts: data ?? [] };
        } catch (err) {
            return { missingProducts: [] };
        }
    };

    return {
        handleSyncAll,
        isSyncingAll: loading,
        syncAllError: error
    };
}

export function bulkSync() {
    const { mutate, loading, error } = useMutation(BULK_SYNC_TO_SHOPIFY, {
        refetchQueries: ['GetProducts']
    });

    const handleBulkSync = async (productIds: string[]) => {
        try {
            const response = await mutate({
                productIds: productIds
            });

            return response?.data?.bulkSyncToShopify;
        } catch (err) {
            alert('Error while syncing all products');
            throw err;
        }
    };

    return {
        handleBulkSync,
        isBulkSyncing: loading,
        bulkSyncError: error
    };
}