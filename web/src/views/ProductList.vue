<script setup lang="ts">
  import { computed, ref } from 'vue';
  import { getAllProducts } from '../composables/useProducts';
  import ProductListTable from "../components/Products/ProductListTable.vue";
  import Pagination from "../components/Pagination.vue";
  import PaginationPerPage from "../components/PaginationPerPage.vue";
  import CreateProductModal from "../components/Products/CreateProductModal.vue";
  import SyncAllProductsModal from "../components/Products/SyncAllProductsModal.vue";

  const page = ref(1);
  const itemsPerPage = ref(10);

  const { pagination, loading } = getAllProducts(itemsPerPage, page);

  const products = computed(() => pagination.value.items);

  const handlePageChange = (newPage: number) => {
    page.value = newPage;
  };

  const handlePerPageChange = (newVal: number) => {
    itemsPerPage.value = newVal;
    page.value = 1;
  };
</script>

<template>
  <h1>Product List</h1>

  <hr class="border border-success border-2 opacity-50">

  <div class="row col-12">
    <div class="col-md-2 col-sm-6">
      <PaginationPerPage
          :per-page="itemsPerPage"
          :disabled="loading"
          @change-per-page="handlePerPageChange"
      />
    </div>
    <div class="col-md-10 col-sm-6 text-end">
      <CreateProductModal :disabled="loading" />
      <SyncAllProductsModal :disabled="loading" />
    </div>
  </div>

  <div v-if="loading">
    <div class="card mt-3 mb-3">
      <div class="card-body text-center">
        <div>
          Loading Product List
        </div>
        <div class="spinner-border mt-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

      </div>
    </div>
  </div>
  <div v-else>
    <ProductListTable :products="products" />
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
@media (max-width: 576px) {
  .action-btn {
    margin-top: 1rem;
  }
}
</style>