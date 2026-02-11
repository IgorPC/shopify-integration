export interface SystemLog {
    action: string
    target: string
    payload: string
    status: boolean
    created_at: string
}

export interface PaginatedLogs {
    items: SystemLog[];
    total: number;
    currentPage: number;
    totalPages: number;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
}