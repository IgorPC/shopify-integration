<script setup lang="ts">
  import Navbar from './components/NavBar.vue';
</script>

<template>
  <div class="app-container">
    <Navbar />

    <main class="main-content">
      <div class="container">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style>
  body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background-color: #f9f9f9;
    color: #333;
  }

  .app-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .main-content {
    flex: 1;
    padding: 2rem;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }

  @media (max-width: 768px) {
    .main-content {
      padding: 1rem;
    }
  }
</style>