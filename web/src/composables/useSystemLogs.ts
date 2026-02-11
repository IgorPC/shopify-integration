import {computed, type Ref} from "vue";
import {useQuery} from "@vue/apollo-composable";
import type {PaginatedLogs} from "../types/systemLog.ts";
import {GET_LOGS} from "../graphql/systemLogs.ts";

export function getAllLogs(perPage: Ref<number> | number = 10, page: Ref<number> | number = 1) {
    const { result, loading, error, refetch } = useQuery<{ allLogs: PaginatedLogs }>(
        GET_LOGS,
        () => ({
            perPage: typeof perPage === 'object' ? perPage.value : perPage,
            page: typeof page === 'object' ? page.value : page
        })
    );

    const pagination = computed<PaginatedLogs>(() => {
        const data = result.value?.allLogs;

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