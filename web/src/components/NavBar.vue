<template>
  <nav class="navbar">
    <div class="navbar-brand">
      <span class="logo">Shopify Integration</span>
    </div>
    <div class="navbar-links">
      <router-link to="/products" class="nav-item">Products</router-link>
      <router-link to="/logs" class="nav-item">System Logs</router-link>
    </div>
  </nav>
</template>

<style scoped>
  .navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 2rem;
    background-color: #1a1a1a;
    color: white;
    height: 64px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .logo {
    font-size: 1.25rem;
    font-weight: bold;
    color: #42b883; /* Verde Vue */
  }

  .navbar-links {
    display: flex;
    gap: 1.5rem;
  }

  .nav-item {
    text-decoration: none;
    color: #e2e2e2;
    font-size: 0.95rem;
    transition: color 0.2s;
  }

  .nav-item:hover, .router-link-active {
    color: #42b883;
  }

  @media (max-width: 600px) {
    .navbar {
      padding: 0 1rem;
    }
  }
</style>