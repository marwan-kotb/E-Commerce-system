<template>
  <div class="auth-card">
    <h2>Login</h2>

    <form @submit.prevent="submit">
      <div class="field">
        <label>Email</label>
        <input v-model="email" type="email" required />
      </div>

      <div class="field">
        <label>Password</label>
        <input v-model="password" type="password" required />
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>

      <p v-if="error" class="error">{{ error }}</p>

      <p class="link">
        No account?
        <RouterLink to="/register">Register</RouterLink>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import api from '../api';

const router = useRouter();
const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const submit = async () => {
  loading.value = true;
  error.value = '';

  try {
    const { data } = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    });

    localStorage.setItem('token', data.access_token);
    router.push({ name: 'Dashboard' });
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Login failed';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-card {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  width: 320px;
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
}

.field {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  color: #111827;
}

label {
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

input {
  padding: 0.4rem 0.6rem;
  border-radius: 4px;
  border: 1px solid #d1d5db;
}

button {
  width: 100%;
  padding: 0.5rem;
  border-radius: 4px;
  border: none;
  background: #2563eb;
  color: #fff;
  cursor: pointer;
}

.error {
  margin-top: 0.5rem;
  color: #b91c1c;
  font-size: 0.85rem;
}

.link {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #111827;
}
</style>
