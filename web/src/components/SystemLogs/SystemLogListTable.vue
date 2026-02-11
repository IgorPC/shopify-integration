<script setup lang="ts">
  import type {SystemLog} from "../../types/systemLog.ts";

  interface Props {
    logs: SystemLog[];
  }

  defineProps<Props>();


</script>

<template>
  <table class="table mt-3">
    <thead>
    <tr>
      <th scope="col" class="w-10">Action</th>
      <th scope="col">Target</th>
      <th scope="col">Message</th>
      <th scope="col">Status</th>
      <th scope="col">Created At</th>
    </tr>
    </thead>
    <tbody>

    <tr v-if="logs.length === 0">
      <td colspan="3" class="text-center text-muted">
        No logs found.
      </td>
    </tr>

    <tr v-for="log in logs" :key="log.payload + log.target + log.created_at">
      <td>{{ log.action }}</td>
      <td>{{ log.target }}</td>
      <td>{{ log.payload }}</td>
      <td>{{ log.status ? "Success" : "Error" }}</td>
      <td>{{ log.created_at }}</td>
    </tr>
    </tbody>
  </table>
</template>

<style scoped>

</style>