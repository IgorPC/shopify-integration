export interface Product {
    id: string;
    title: string;
    description: string;
    price: number;
    inventoryQuantity: number;
}

export interface PaginatedProducts {
    items: Product[];
    total: number;
    currentPage: number;
    totalPages: number;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
}

export interface MissingProduct {
    id: string;
    title: string;
}

export interface SyncAllResponse {
    syncAll: {
        message: string;
        syncCount: number;
        missingProducts: MissingProduct[];
    }
}