<template>
  <div class="max-w-sm mx-auto mt-24">
    <h1 class="text-3xl font-bold text-amber-400 mb-8 text-center">Ransom Notes</h1>
    <form
      class="bg-stone-800/60 border border-stone-600 rounded-2xl p-8 shadow-xl"
      @submit.prevent="submit"
    >
      <h2 class="text-lg font-semibold text-stone-200 mb-6">Create account</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm text-stone-400 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-lg bg-stone-900 border border-stone-600 px-4 py-2 text-stone-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          />
        </div>
        <div>
          <label class="block text-sm text-stone-400 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg bg-stone-900 border border-stone-600 px-4 py-2 text-stone-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          />
        </div>
        <div>
          <label class="block text-sm text-stone-400 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full rounded-lg bg-stone-900 border border-stone-600 px-4 py-2 text-stone-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          />
        </div>
        <div>
          <label class="block text-sm text-stone-400 mb-1">Confirm password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full rounded-lg bg-stone-900 border border-stone-600 px-4 py-2 text-stone-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
          />
        </div>
      </div>
      <p v-if="error" class="mt-4 text-red-400 text-sm">{{ error }}</p>
      <button
        type="submit"
        class="mt-6 w-full rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold py-2.5 transition"
        :disabled="loading"
      >
        {{ loading ? 'Creating…' : 'Register' }}
      </button>
      <p class="mt-4 text-center text-stone-500 text-sm">
        Already have an account?
        <router-link to="/login" class="text-amber-400 hover:underline">Log in</router-link>
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { setUser } = useAuth();
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const error = ref('');
const loading = ref(false);

async function submit() {
  error.value = '';
  loading.value = true;
  try {
    const { data } = await api.post('/register', form);
    localStorage.setItem('token', data.token);
    setUser(data.user);
    router.push('/');
  } catch (e) {
    const msg = e.response?.data?.errors;
    error.value = msg
      ? Object.values(msg).flat().join(' ')
      : e.response?.data?.message ?? 'Registration failed.';
  } finally {
    loading.value = false;
  }
}
</script>
