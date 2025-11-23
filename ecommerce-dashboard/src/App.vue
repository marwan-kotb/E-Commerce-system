<template>
  <div class="app">
    <header class="app-header">
      <div class="header-left">
        <span class="logo-dot" />
        <h1>E-Commerce Mini Admin</h1>
      </div>

      <div class="header-right">
        <span v-if="isAuth" class="user-pill">
          Logged in
        </span>
        <button v-if="isAuth" class="btn btn-danger" @click="logout">
          Logout
        </button>
      </div>
    </header>

    <div class="app-body">
      <nav v-if="isAuth" class="sidebar">
        <RouterLink to="/" class="nav-link">Dashboard</RouterLink>
        <RouterLink to="/products" class="nav-link">Products & Cart</RouterLink>
        <RouterLink to="/orders" class="nav-link">Orders</RouterLink>
      </nav>

      <main class="content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, RouterView, RouterLink } from 'vue-router';
import api from './api';

const router = useRouter();

const isAuth = computed(() => !!localStorage.getItem('token'));

const logout = async () => {
  try {
    await api.post('/auth/logout');
  } catch (e) {
    // ignore
  }
  localStorage.removeItem('token');
  router.push({ name: 'Login' });
};
</script>

<style scoped>
.app {
  height: 100vh;
  display: flex;
  flex-direction: column;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #0f172a;
}

.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: radial-gradient(circle at top left, #2563eb, #0f172a);
  color: #e5e7eb;
  border-bottom: 1px solid rgba(148, 163, 184, 0.4);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-dot {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: conic-gradient(from 180deg, #60a5fa, #22c55e, #f97316, #60a5fa);
  box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.5);
}

.app-header h1 {
  font-size: 1rem;
  font-weight: 600;
}

.header-right {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.user-pill {
  font-size: 0.85rem;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.6);
}

.app-body {
  flex: 1;
  display: flex;
  min-height: 0;
}

.sidebar {
  width: 210px;
  background: #020617;
  border-right: 1px solid rgba(148, 163, 184, 0.4);
  display: flex;
  flex-direction: column;
  padding: 1rem 0.75rem;
  gap: 0.3rem;
}

.nav-link {
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  color: #e5e7eb;
  text-decoration: none;
  font-size: 0.9rem;
}

.nav-link.router-link-active {
  background: linear-gradient(90deg, #2563eb, #22c55e);
  color: white;
}

.content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
  background: radial-gradient(circle at top, #0b1120, #020617);
  color: #e5e7eb;
}

/* Buttons */
.btn {
  padding: 0.35rem 0.9rem;
  border-radius: 6px;
  border: none;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}
</style>
