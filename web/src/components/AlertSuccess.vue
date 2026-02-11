<script setup lang="ts">
  defineProps<{
    text: string;
  }>();

  const randomString = Math.random().toString(36).substring(2, 10);
</script>

<template>
  <div :id="randomString" class="alert alert-success text-start" role="alert">
    {{ text }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="float: right"></button>
  </div>
</template>

<style scoped>

</style>