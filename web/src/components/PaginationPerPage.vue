<script setup lang="ts">
  defineProps<{
    perPage: number;
  }>();

  const emit = defineEmits<{
    (e: 'changePerPage', value: number): void
  }>();

  const onSelectChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('changePerPage', Number(target.value));
  };

</script>

<template>
  <select
      class="form-select"
      aria-label="Per Page"
      :value="perPage"
      @change="onSelectChange"
  >
    <option value="2">2</option>
    <option selected value="10">10</option>
    <option value="20">20</option>
    <option value="30">30</option>
    <option value="50">50</option>
  </select>
</template>

<style scoped>

</style>