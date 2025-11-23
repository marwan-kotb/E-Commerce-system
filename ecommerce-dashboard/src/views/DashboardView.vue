<template>
  <div class="page">
    <h2 class="page-title">Dashboard Overview</h2>
    <p class="page-subtitle">
      Quick summary of your store activity.
    </p>

    <!-- Stats cards -->
    <div class="cards">
      <div class="card">
        <div class="card-label">Total Products</div>
        <div class="card-value">{{ totalProducts }}</div>
      </div>

      <div class="card">
        <div class="card-label">Total Orders</div>
        <div class="card-value">{{ totalOrders }}</div>
      </div>
    </div>

    <!-- Quick navigation -->
    <div class="quick-actions">
      <RouterLink to="/products" class="qa-card">
        <div class="qa-title">Manage Products & Cart</div>
        <div class="qa-subtitle">Create, edit, and add items to your cart.</div>
      </RouterLink>

      <RouterLink to="/orders" class="qa-card">
        <div class="qa-title">View Orders</div>
        <div class="qa-subtitle">Review recent orders and their details.</div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import api from '../api';

const totalProducts = ref(0);
const totalOrders = ref(0);

onMounted(async () => {
  try {
    const [productsRes, ordersRes] = await Promise.all([
      api.get('/products'),
      api.get('/orders'),
    ]);
    totalProducts.value = productsRes.data.length;
    totalOrders.value = ordersRes.data.length;
  } catch (e) {
    console.error(e);
  }
});
</script>

<style scoped>
.page {
  max-width: 900px;
  margin: 0 auto;
}

.page-title {
  font-size: 1.3rem;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  font-size: 0.9rem;
  color: #9ca3af;
  margin-bottom: 1.25rem;
}

.cards {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

.card {
  flex: 1;
  min-width: 220px;
  background: linear-gradient(145deg, #020617, #111827);
  border-radius: 10px;
  padding: 1rem 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.8);
}

.card-label {
  font-size: 0.9rem;
  color: #9ca3af;
}

.card-value {
  margin-top: 0.4rem;
  font-size: 1.8rem;
  font-weight: 600;
}

/* Quick actions */
.quick-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.qa-card {
  flex: 1;
  min-width: 220px;
  background: rgba(15, 23, 42, 0.95);
  border-radius: 10px;
  padding: 0.9rem 1.1rem;
  border: 1px solid rgba(148, 163, 184, 0.5);
  text-decoration: none;
  color: #e5e7eb;
  transition: transform 0.12s ease-out, box-shadow 0.12s ease-out,
    border-color 0.12s ease-out, background 0.12s ease-out;
}

.qa-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.9);
  border-color: #2563eb;
  background: radial-gradient(circle at top left, #1d283a, #020617);
}

.qa-title {
  font-size: 0.95rem;
  font-weight: 500;
  margin-bottom: 0.2rem;
}

.qa-subtitle {
  font-size: 0.8rem;
  color: #9ca3af;
}
</style>
