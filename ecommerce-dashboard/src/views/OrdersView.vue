<template>
  <div class="page">
    <h2 class="page-title">Orders</h2>
    <p class="page-subtitle">
      Review placed orders and their items.
    </p>

    <section class="panel">
      <div class="panel-header">
        <h3>Orders List</h3>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Order #</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Details</th>
            </tr>
          </thead>

          <tbody>
            <template v-for="order in orders" :key="order.id">
              <tr>
                <td>{{ order.id }}</td>
                <td>{{ order.order_number }}</td>
                <td>{{ order.total }}</td>
                <td>{{ order.status }}</td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>
                  <button class="btn btn-small btn-outline" @click="toggleDetails(order.id)">
                    {{ openOrderId === order.id ? 'Hide' : 'Show' }}
                  </button>
                </td>
              </tr>
              <tr v-if="openOrderId === order.id">
                <td colspan="6">
                  <div class="details">
                    <h4>Items</h4>
                    <table class="inner-table">
                      <thead>
                        <tr>
                          <th>Product</th>
                          <th>Qty</th>
                          <th>Price</th>
                          <th>Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in order.items" :key="item.id">
                          <td>{{ item.product?.name ?? ('#' + item.product_id) }}</td>
                          <td>{{ item.quantity }}</td>
                          <td>{{ item.price }}</td>
                          <td>{{ item.subtotal }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </template>

            <tr v-if="!orders.length">
              <td colspan="6" class="empty-cell">
                No orders yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const orders = ref([]);
const openOrderId = ref(null);

const fetchOrders = async () => {
  const { data } = await api.get('/orders');
  orders.value = data;
};

const toggleDetails = (orderId) => {
  openOrderId.value = openOrderId.value === orderId ? null : orderId;
};

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleString();
};

onMounted(fetchOrders);
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.page-title {
  font-size: 1.2rem;
  margin-bottom: 0.15rem;
}

.page-subtitle {
  font-size: 0.9rem;
  color: #9ca3af;
}

.panel {
  background: rgba(15, 23, 42, 0.95);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.9);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

th,
td {
  padding: 0.45rem 0.5rem;
  border-bottom: 1px solid rgba(55, 65, 81, 0.9);
}

th {
  text-align: left;
  color: #9ca3af;
}

.empty-cell {
  text-align: center;
  padding: 0.8rem;
  color: #9ca3af;
}

.details {
  margin-top: 0.3rem;
  background: rgba(15, 23, 42, 0.9);
  border-radius: 8px;
  padding: 0.6rem 0.7rem;
  border: 1px solid rgba(55, 65, 81, 0.9);
}

.inner-table {
  width: 100%;
  border-collapse: collapse;
}

.inner-table th,
.inner-table td {
  border-bottom: 1px solid rgba(55, 65, 81, 0.7);
}

.btn {
  border: none;
  border-radius: 999px;
  padding: 0.3rem 0.7rem;
  font-size: 0.78rem;
  cursor: pointer;
}

.btn-outline {
  background: transparent;
  border: 1px solid #4b5563;
  color: #e5e7eb;
}
</style>
