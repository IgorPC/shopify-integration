<script setup lang="ts">
  import type { Product } from '../../types/product';
  import UpdateProductModal from "./UpdateProductModal.vue";

  interface Props {
    products: Product[];
  }

  defineProps<Props>();
</script>

<template>
  <table class="table mt-3">
    <thead>
    <tr>
      <th scope="col" class="w-50">Title</th>
      <th scope="col" style="width: 150px">Price</th>
      <th scope="col" style="width: 100px">Quantity</th>
      <th scope="col" style="width: 80px"></th>
    </tr>
    </thead>
    <tbody>

      <tr v-if="products.length === 0">
        <td colspan="3" class="text-center text-muted">
          No products found.
        </td>
      </tr>

      <tr v-for="product in products" :key="product.id">
        <td>{{ product.title }}</td>
        <td class="fw-bold text-success">
          {{ product.price.toLocaleString('us', { style: 'currency', currency: 'USD' }) }}
        </td>
        <td>{{ product.inventoryQuantity }}</td>
        <td>
          <UpdateProductModal :id="product.id"/>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>

</style>