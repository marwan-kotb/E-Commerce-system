<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Products & Cart</h2>
        <p class="page-subtitle">
          Manage products and create orders using your cart.
        </p>
      </div>
    </div>

    <div class="grid">
      <!-- Left: Product management -->
      <section class="panel">
        <div class="panel-header">
          <h3>Product Form</h3>
          <span v-if="form.id" class="badge badge-outline">Editing #{{ form.id }}</span>
        </div>

        <form class="form" @submit.prevent="saveProduct">
          <div class="field">
            <label>Name</label>
            <input v-model="form.name" required />
          </div>

          <div class="field">
            <label>Description</label>
            <textarea v-model="form.description" rows="2"></textarea>
          </div>

          <div class="field-row">
            <div class="field">
              <label>Price</label>
              <input v-model.number="form.price" type="number" step="0.01" min="0" required />
            </div>
            <div class="field">
              <label>Stock</label>
              <input v-model.number="form.stock" type="number" min="0" required />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              {{ form.id ? 'Update product' : 'Create product' }}
            </button>
            <button v-if="form.id" type="button" class="btn btn-secondary" @click="resetForm">
              Cancel
            </button>
          </div>
        </form>

        <div class="panel-separator" />

        <div class="panel-header">
          <h3>Products List</h3>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th style="width: 180px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td>{{ product.id }}</td>
                <td>{{ product.name }}</td>
                <td>{{ product.price }}</td>
                <td>{{ product.stock }}</td>
                <td>
                  <span
                    :class="[
                      'badge',
                      product.status === 'out_of_stock' ? 'badge-red' : 'badge-green'
                    ]"
                  >
                    {{ product.status }}
                  </span>
                </td>
                <td>
                  <div class="actions">
                    <button class="btn btn-small" @click="editProduct(product)">
                      Edit
                    </button>
                    <button class="btn btn-small btn-danger" @click="deleteProduct(product.id)">
                      Delete
                    </button>
                  </div>

                  <div class="cart-inline">
                    <input
                      v-model.number="quantities[product.id]"
                      type="number"
                      min="1"
                      :max="product.stock"
                      class="qty-input"
                      :disabled="product.stock === 0"
                    />
                    <button
                      class="btn btn-small btn-outline"
                      :disabled="product.stock === 0"
                      @click="addToCart(product)"
                    >
                      Add to cart
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!products.length">
                <td colspan="6" class="empty-cell">
                  No products yet. Create your first product above.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Right: Cart & place order -->
      <section class="panel panel-cart">
        <div class="panel-header">
          <h3>Your Cart</h3>
          <span class="badge badge-outline">{{ cartItems.length }} item(s)</span>
        </div>

        <div v-if="cartItems.length" class="table-wrapper small">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in cartItems" :key="item.id">
                <td>{{ item.product?.name ?? ('#' + item.product_id) }}</td>
                <td>{{ item.quantity }}</td>
                <td>{{ item.product?.price ?? '-' }}</td>
                <td>
                  {{
                    item.product
                      ? (item.product.price * item.quantity).toFixed(2)
                      : '-'
                  }}
                </td>
                <td>
                  <button class="btn btn-small btn-danger" @click="removeFromCart(item.id)">
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="empty-text">
          Your cart is empty. Use "Add to cart" on the products table.
        </p>

        <div class="cart-summary">
          <div class="summary-row">
            <span>Total</span>
            <span class="total-value">{{ cartTotal.toFixed(2) }}</span>
          </div>
        </div>

        <div class="panel-separator" />

        <div class="panel-header">
          <h3>Place Order</h3>
        </div>

        <form class="form" @submit.prevent="placeOrder">
          <div class="field">
            <label>Address</label>
            <input v-model="orderForm.address" placeholder="Delivery address" />
          </div>
          <div class="field">
            <label>Phone</label>
            <input v-model="orderForm.phone" placeholder="Contact phone" />
          </div>

          <button
            class="btn btn-primary"
            type="submit"
            :disabled="!cartItems.length || !orderForm.address || !orderForm.phone || placingOrder"
          >
            {{ placingOrder ? 'Placing order...' : 'Place order' }}
          </button>

          <p v-if="orderError" class="error-text">
            {{ orderError }}
          </p>

          <div v-if="orderSuccess" class="success-box">
            <div>Order placed successfully 🎉</div>
            <div>Order #: <strong>{{ orderSuccess.order_number }}</strong></div>
            <div>Total: <strong>{{ orderSuccess.total }}</strong></div>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import api from '../api';

const products = ref([]);
const cartItems = ref([]);

const quantities = reactive({});

const form = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  stock: 0,
});

const orderForm = reactive({
  address: '',
  phone: '',
});

const placingOrder = ref(false);
const orderError = ref('');
const orderSuccess = ref(null);

const fetchProducts = async () => {
  const { data } = await api.get('/products');
  products.value = data;

  data.forEach((p) => {
    if (!quantities[p.id]) {
      quantities[p.id] = 1;
    }
  });
};

const fetchCart = async () => {
  const { data } = await api.get('/cart');
  cartItems.value = data;
};

const resetForm = () => {
  form.value = {
    id: null,
    name: '',
    description: '',
    price: 0,
    stock: 0,
  };
};

const saveProduct = async () => {
  if (form.value.id) {
    await api.put(`/products/${form.value.id}`, {
      name: form.value.name,
      description: form.value.description,
      price: form.value.price,
      stock: form.value.stock,
    });
  } else {
    await api.post('/products', {
      name: form.value.name,
      description: form.value.description,
      price: form.value.price,
      stock: form.value.stock,
    });
  }
  await fetchProducts();
  resetForm();
};

const editProduct = (product) => {
  form.value = { ...product };
};

const deleteProduct = async (id) => {
  if (!confirm('Delete this product?')) return;
  await api.delete(`/products/${id}`);
  await fetchProducts();
};

const addToCart = async (product) => {
  const qty = quantities[product.id] || 1;
  try {
    await api.post('/cart', {
      product_id: product.id,
      quantity: qty,
    });
    await fetchCart();
  } catch (e) {
    console.error(e);
  }
};

const removeFromCart = async (cartItemId) => {
  await api.delete(`/cart/${cartItemId}`);
  await fetchCart();
};

const cartTotal = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    const price = item.product?.price ?? 0;
    return sum + price * item.quantity;
  }, 0);
});

const placeOrder = async () => {
  orderError.value = '';
  orderSuccess.value = null;

  if (!cartItems.value.length) {
    orderError.value = 'Cart is empty.';
    return;
  }

  placingOrder.value = true;
  try {
    const { data } = await api.post('/orders', {
      address: orderForm.address,
      phone: orderForm.phone,
    });

    orderSuccess.value = data;
    orderForm.address = '';
    orderForm.phone = '';
    await fetchCart();
    await fetchProducts(); // stock updated
  } catch (e) {
    orderError.value =
      e.response?.data?.message ??
      'Failed to place order. Please check stock and try again.';
  } finally {
    placingOrder.value = false;
  }
};

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchCart()]);
});
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.page-title {
  font-size: 1.2rem;
  margin-bottom: 0.15rem;
}

.page-subtitle {
  font-size: 0.9rem;
  color: #9ca3af;
}

.grid {
  display: grid;
  grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.3fr);
  gap: 1.25rem;
}

@media (max-width: 900px) {
  .grid {
    grid-template-columns: minmax(0, 1fr);
  }
}

.panel {
  background: rgba(15, 23, 42, 0.95);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.9);
}

.panel-cart {
  max-height: 100%;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.panel-separator {
  height: 1px;
  background: rgba(55, 65, 81, 0.8);
  margin: 0.75rem 0;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-row {
  display: flex;
  gap: 0.75rem;
}

.field-row .field {
  flex: 1;
}

label {
  font-size: 0.85rem;
  color: #9ca3af;
}

input,
textarea {
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(75, 85, 99, 0.9);
  border-radius: 8px;
  padding: 0.4rem 0.6rem;
  color: #e5e7eb;
  font-size: 0.9rem;
}

input:focus,
textarea:focus {
  outline: none;
  border-color: #2563eb;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
}

.table-wrapper {
  margin-top: 0.5rem;
  overflow-x: auto;
}

.table-wrapper.small {
  max-height: 220px;
  overflow-y: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

th,
td {
  padding: 0.4rem 0.5rem;
  border-bottom: 1px solid rgba(55, 65, 81, 0.9);
}

th {
  text-align: left;
  font-weight: 500;
  color: #9ca3af;
}

.empty-cell {
  text-align: center;
  padding: 0.75rem;
  color: #9ca3af;
}

.actions {
  display: flex;
  gap: 0.35rem;
  margin-bottom: 0.3rem;
}

.cart-inline {
  display: flex;
  gap: 0.35rem;
  align-items: center;
}

.qty-input {
  width: 60px;
}

.btn {
  border: none;
  border-radius: 999px;
  padding: 0.32rem 0.7rem;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb, #22c55e);
  color: white;
}

.btn-secondary {
  background: #4b5563;
  color: #e5e7eb;
}

.btn-outline {
  background: transparent;
  border: 1px solid #4b5563;
  color: #e5e7eb;
}

.btn-danger {
  background: #b91c1c;
  color: white;
}

.btn-small {
  padding: 0.25rem 0.5rem;
  font-size: 0.78rem;
}

.badge {
  padding: 0.12rem 0.5rem;
  border-radius: 999px;
  font-size: 0.7rem;
  text-transform: uppercase;
}

.badge-outline {
  border: 1px solid #4b5563;
  color: #9ca3af;
}

.badge-green {
  background: rgba(22, 163, 74, 0.15);
  color: #4ade80;
}

.badge-red {
  background: rgba(220, 38, 38, 0.2);
  color: #fca5a5;
}

.cart-summary {
  margin-top: 0.75rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.total-value {
  font-weight: 600;
}

.empty-text {
  font-size: 0.85rem;
  color: #9ca3af;
  margin-top: 0.5rem;
}

.error-text {
  margin-top: 0.4rem;
  color: #fca5a5;
  font-size: 0.85rem;
}

.success-box {
  margin-top: 0.6rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  background: rgba(22, 163, 74, 0.18);
  border: 1px solid rgba(34, 197, 94, 0.7);
  font-size: 0.85rem;
}
</style>
