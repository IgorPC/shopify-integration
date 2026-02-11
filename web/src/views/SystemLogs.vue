<script setup lang="ts">
import {computed, ref} from "vue";
  import PaginationPerPage from "../components/PaginationPerPage.vue";
  import {getAllLogs} from "../composables/useSystemLogs.ts";
  import SystemLogListTable from "../components/SystemLogs/SystemLogListTable.vue";
import ProductListTable from "../components/Products/ProductListTable.vue";
import Pagination from "../components/Pagination.vue";

  const page = ref(1);
  const itemsPerPage = ref(10);

  const { pagination, loading } = getAllLogs(itemsPerPage, page);

  const logs = computed(() => pagination.value.items);

  const handlePageChange = (newPage: number) => {
    page.value = newPage;
  };

  const handlePerPageChange = (newVal: number) => {
    itemsPerPage.value = newVal;
    page.value = 1;
  };
</script>

<template>
  <h1>System Logs</h1>

  <hr class="border border-success border-2 opacity-50">

  <div class="row col-12">
    <div class="col-md-2 col-sm-6">
      <PaginationPerPage
          :per-page="itemsPerPage"
          :disabled="loading"
          @change-per-page="handlePerPageChange"
      />
    </div>
  </div>

  <div v-if="loading">
    <div class="card mt-3 mb-3">
      <div class="card-body text-center">
        <div>
          Loading System Logs
        </div>
        <div class="spinner-border mt-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

      </div>
    </div>
  </div>
  <div v-else>
    <SystemLogListTable :logs="logs" />
  </div>

  <Pagination
      v-if="!loading"
      :current-page="pagination.currentPage"
      :total-pages="pagination.totalPages"
      :has-next-page="pagination.hasNextPage"
      :has-previous-page="pagination.hasPreviousPage"
      :loading="loading"
      @change-page="handlePageChange"
  />

</template>

<style scoped>

</style>