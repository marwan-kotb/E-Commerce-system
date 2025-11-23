<template>
  <div class="auth-card">
    <h2>Register</h2>

    <form @submit.prevent="submit">
      <div class="field">
        <label>Name</label>
        <input v-model="name" type="text" required />
      </div>

      <div class="field">
        <label>Email</label>
        <input v-model="email" type="email" required />
      </div>

      <div class="field">
        <label>Password</label>
        <input v-model="password" type="password" required minlength="6" />
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Registering...' : 'Register' }}
      </button>

      <p v-if="error" class="error">{{ error }}</p>

      <p class="link">
        Already have an account?
        <RouterLink to="/login">Login</RouterLink>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import api from '../api';

const router = useRouter();
const name = ref('');
const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const submit = async () => {
  loading.value = true;
  error.value = '';

  try {
    await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
    });

    // Auto-login or redirect to login; keep it simple:
    router.push({ name: 'Login' });
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Registration failed';
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
  box-shadow: #10b981;
}

label {
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
  color: #111827;
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
  background: #10b981;
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
