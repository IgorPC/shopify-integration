<script setup lang="ts">
  import { syncAll } from "../../composables/useProducts.ts";
  import {onMounted, onUnmounted, ref} from "vue";
  import { Modal } from 'bootstrap';
  import type {MissingProduct} from "../../types/product.ts";
  import { bulkSync } from "../../composables/useProducts.ts";

  defineProps<{
    disabled: boolean;
  }>();

  const { handleSyncAll, isSyncingAll } = syncAll();
  const { handleBulkSync, isBulkSyncing } = bulkSync();

  const missingProducts = ref<MissingProduct[]>([]);
  const hasMissingProducts = ref(false);
  const selectedProducts = ref<string[]>([]);

  const resetState = () => {
    selectedProducts.value = [];
    missingProducts.value = [];
    hasMissingProducts.value = false;
  };

  onMounted(() => {
    const modalEl = document.getElementById('sync-all-modal');
    modalEl?.addEventListener('hidden.bs.modal', resetState);
  });

  onUnmounted(() => {
    const modalEl = document.getElementById('sync-all-modal');
    modalEl?.removeEventListener('hidden.bs.modal', resetState);
  });

  const openModal = () => {
    const modalEl = document.getElementById(`sync-all-modal`);
    if (modalEl) {

      let modal = Modal.getInstance(modalEl);
      if (!modal) {
        modal = new Modal(modalEl);
      }
      modal.show();
    }
  }

  const selectProduct = (id: string) => {
    if (selectedProducts.value.includes(id)) {
      selectedProducts.value = selectedProducts.value.filter(itemId => itemId !== id);
    } else {
      selectedProducts.value.push(id);
    }
  }

  const runFullSync = async () => {
    const data = await handleSyncAll();

    missingProducts.value = data.missingProducts;
    hasMissingProducts.value = data.missingProducts.length > 0;

    openModal()
  };

  const syncAllProducts = async () => {
    if (selectedProducts.value.length === 0) return;

    await handleBulkSync(selectedProducts.value);

    const modalEl = document.getElementById('sync-all-modal');
    if (modalEl) {
      const modal = Modal.getInstance(modalEl);
      setTimeout(() => modal?.hide(), 500);
    }
  }
</script>

<template>
  <button
      type="button"
      :disabled="disabled || isSyncingAll"
      class="btn btn-success action-btn"
      @click="runFullSync()"
  >
    {{ isSyncingAll ? "Syncing products..." : "Sync all products" }}
  </button>

  <!-- Modal -->
  <div class="modal fade" id="sync-all-modal" tabindex="-1" aria-labelledby="sync-all-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="sync-all-modal-label">Sync Products</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-start">
          <div v-if="hasMissingProducts">
            <h5>List of Missing Products:</h5>
            <br>
            <div class="row text-center">
              <div class="col-12">
                <button type="button" class="btn btn-success w-100" :disabled="selectedProducts.length <= 0 || isBulkSyncing" @click="syncAllProducts()">{{ isBulkSyncing ? "Fixing..." : "Fix Products" }}</button>
              </div>
            </div>
            <br>
            <div class="text-end">
              <span>Selected: {{ selectedProducts.length }}/10</span>
            </div>
            <br>
            <ul class="list-group">
              <li v-for="product in missingProducts" :key="product.id" class="list-group-item">
                <input
                    :disabled="selectedProducts.length >= 10 && ! selectedProducts.includes(product.id)"
                    class="form-check-input me-1"
                    type="checkbox"
                    :checked="selectedProducts.includes(product.id)"
                    @click="selectProduct(product.id)"
                    :id="product.id"
                >
                <label class="form-check-label" :for="product.id"> {{ product.title }}</label>
              </li>
            </ul>
          </div>
          <div v-else>
            <div class="alert alert-success" role="alert">
              <h4 class="alert-heading">Well done!</h4>
              <p>Your Shopify store is synced with the local database.</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>