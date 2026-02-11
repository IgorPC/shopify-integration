<script setup lang="ts">

  import {QuillEditor} from "@vueup/vue-quill";
  import { ref, computed } from "vue";
  import { getProduct } from '../../composables/useProducts';
  import { Modal } from 'bootstrap';
  import { updateProduct } from "../../composables/useProducts.ts";
  import { deleteProduct } from "../../composables/useProducts.ts";
  import { syncProduct } from "../../composables/useProducts.ts";
  import { getAllProducts } from "../../composables/useProducts";
  import type { Product } from "../../types/product.ts";
  import AlertSuccess from "../AlertSuccess.vue";

  const props = defineProps<{
    id: string;
  }>();

  const { handleUpdate, isSaving } = updateProduct();
  const { handleDelete, isDeleting } = deleteProduct();
  const { handleSync, isSyncing } = syncProduct();
  const { refetch: refetchList } = getAllProducts();

  const isFormValid = computed(() => {
    const hasTitle = title.value.trim().length >= 5;
    const hasDescription = description.value.trim().length > 0;
    const hasPrice = price.value > 0;

    return hasTitle && hasDescription && hasPrice;
  });

  const editorKey = ref(0);

  const title = ref('')
  const price = ref(0.0);
  const description = ref('')
  const success = ref(false);

  const { fetchProduct, loading, onResult } = getProduct();

  onResult((queryResult) => {
    if (queryResult.data?.product) {
      const p = queryResult.data.product;
      title.value = p.title;
      price.value = p.price;
      description.value = p.description;

      editorKey.value++;

      const modalEl = document.getElementById(`update-product-modal-${props.id}`);
      if (modalEl) {
        const modal = Modal.getOrCreateInstance(modalEl);
        modal.show();
      }
    }
  });

  const openModal = async () => {
    await fetchProduct(props.id);

    const modalEl = document.getElementById(`update-product-modal-${props.id}`);

    if (modalEl && ! modalEl.classList.contains('show')) {
      const modal = Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  };

  const save = async () => {
    if (! isFormValid) return;

    const product: Omit<Product, 'inventoryQuantity'> = {
      id: props.id,
      title: title.value,
      description: description.value,
      price: price.value,
    }

    const updatedProduct = await handleUpdate(product);

    if (updatedProduct) {
      success.value = true;
      editorKey.value++;
    }
  }

  const deleteProductBtn = async () => {
    const deleted = await handleDelete(props.id);

    if (deleted) {
      closeModal()
    }
  }

  const sync = async () => {
    await handleSync(props.id);
    await refetchList();

    closeModal()
  };

  const closeModal = () => {
    const modalEl = document.getElementById(`update-product-modal-${props.id}`);

    if (modalEl) {
      setTimeout(() => {
        const modal = Modal.getOrCreateInstance(modalEl);
        modal.hide();
      }, 1000)
    }
  }

</script>

<template>
  <button type="button" class="btn btn-warning" :disabled="loading" @click="openModal()">{{ loading ? 'Loading...' : 'Edit' }}</button>

  <div class="modal fade" :id="'update-product-modal-' + props.id" tabindex="-1" aria-labelledby="update-product-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="update-product-modal-label">Update Product</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class=" mb-4 row text-center">
            <div class="col-md-6 mt-2">
              <button type="button" :disabled="isDeleting" class="btn btn-danger w-100" @click="deleteProductBtn()">{{ isDeleting ? 'Deleting...' : 'Delete' }}</button>
            </div>
            <div class="col-md-6 mt-2">
              <button type="button" class="btn btn-success w-100" :disabled="isSyncing" @click="sync()">{{ isSyncing ? 'Syncing...' : 'Sync' }}</button>
            </div>
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text" id="inputGroup-sizing-default">Title</span>
            <input v-model="title" type="text" class="form-control">
          </div>
          <div class="text-start mb-3">
            <label class="form-label">Product Description</label>
            <QuillEditor
                :key="editorKey"
                v-model:content="description"
                contentType="html"
                theme="snow"
            />
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text" style="width: 86px">Price $</span>
            <input v-model="price" type="number" class="form-control" aria-label="Amount (to the nearest dollar)">
            <span class="input-group-text">.00</span>
          </div>

          <AlertSuccess
              v-if="success"
              text="Product Successfully Updated"
          />

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" :disabled="isSaving" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" :disabled="!isFormValid || isSaving" @click="save()">{{ isSaving ? 'Saving...' : 'Save' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  :deep(.ql-container) {
    height: 250px;
    background: white;
    font-size: 1rem;
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
  }

  :deep(.ql-toolbar) {
    background: #f8f9fa;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
  }
</style>