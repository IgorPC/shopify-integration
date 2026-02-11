<script setup lang="ts">
    import { ref, computed } from "vue";
    import { QuillEditor } from '@vueup/vue-quill'
    import '@vueup/vue-quill/dist/vue-quill.snow.css';
    import type {Product} from "../../types/product.ts";
    import { createProduct } from "../../composables/useProducts.ts";
    import AlertSuccess from "../AlertSuccess.vue";

    defineProps<{
      disabled: boolean;
    }>();

    const { handleCreate, isSaving } = createProduct();

    const editorKey = ref(0);
    const title = ref('');
    const price = ref(0.0);
    const description = ref('')
    const quantity = ref(0);
    const success = ref(false);

    const isFormValid = computed(() => {
      const hasTitle = title.value.trim().length >= 5;
      const hasDescription = description.value.trim().length > 0;
      const hasPrice = price.value > 0;
      const hasQuantity = quantity.value > 0;

      return hasTitle && hasDescription && hasPrice && hasQuantity;
    });

    const save = async () => {
      if (! isFormValid) return;

      const newProduct: Omit<Product, 'id'> = {
        title: title.value,
        description: description.value,
        price: price.value,
        inventoryQuantity: quantity.value,
      }

      const product = await handleCreate(newProduct);

      if (product) {
        title.value = '';
        description.value = '';
        price.value = 0.0;
        quantity.value = 0;

        success.value = true;
        editorKey.value++;
      }
    };

</script>

<template>

  <button type="button" :disabled="disabled" class="btn btn-primary me-3 action-btn" data-bs-toggle="modal" data-bs-target="#create-product-modal">Add new Product</button>

  <div class="modal fade" id="create-product-modal" tabindex="-1" aria-labelledby="create-product-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="create-product-modal-label">Create Product</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
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
          <div class="input-group mb-3">
            <span class="input-group-text" id="inputGroup-sizing-default">Quantity</span>
            <input v-model="quantity" type="number" class="form-control">
          </div>

          <AlertSuccess
            v-if="success"
            text="Product Successfully Created"
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