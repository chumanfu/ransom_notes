<template>
  <div class="min-h-screen bg-stone-950 text-stone-100 font-sans">
    <AppHeader v-if="user" :user="user" @logout="handleLogout" />
    <main class="container mx-auto px-4 py-6">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppHeader from './components/AppHeader.vue';
import { useAuth } from './composables/useAuth';

const router = useRouter();
const { user, fetchUser, logout } = useAuth();

onMounted(() => {
  const token = localStorage.getItem('token');
  if (token && !user.value) fetchUser();
});

const handleLogout = async () => {
  await logout();
  router.push('/login');
};
</script>
