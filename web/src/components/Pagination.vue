<script setup lang="ts">
  defineProps<{
    currentPage: number;
    totalPages: number;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
    loading?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'changePage', page: number): void
  }>();

  const goToPage = (page: number) => {
    if (page < 1) return;
    emit('changePage', page);
  };
</script>

<template>
  <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
      <li class="page-item" :class="{ disabled: !hasPreviousPage || loading }">
        <button class="page-link" @click="goToPage(currentPage - 1)" aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
        </button>
      </li>

      <li class="page-item active">
        <span class="page-link">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
      </li>

      <li class="page-item" :class="{ disabled: !hasNextPage || loading }">
        <button class="page-link" @click="goToPage(currentPage + 1)" aria-label="Next">
          <span aria-hidden="true">&raquo;</span>
        </button>
      </li>
    </ul>
  </nav>
</template>

<style scoped>

</style>