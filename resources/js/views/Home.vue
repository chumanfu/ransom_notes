<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
      <h1 class="text-2xl font-bold text-stone-100">Games</h1>
      <div class="flex gap-3">
        <button
          v-if="user?.is_admin"
          type="button"
          class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2 transition"
          @click="createGame"
        >
          Create game
        </button>
        <div class="flex gap-2">
          <input
            v-model="joinCode"
            type="text"
            placeholder="Game code"
            maxlength="6"
            class="w-28 rounded-lg bg-stone-800 border border-stone-600 px-3 py-2 text-stone-100 uppercase placeholder:text-stone-500 focus:border-amber-500 outline-none"
          />
          <button
            type="button"
            class="rounded-lg bg-stone-600 hover:bg-stone-500 text-stone-100 font-medium px-4 py-2 transition"
            @click="joinGame"
          >
            Join
          </button>
        </div>
      </div>
    </div>
    <p v-if="joinError" class="text-red-400 text-sm mb-4">{{ joinError }}</p>
    <ul class="space-y-3">
      <li
        v-for="g in games"
        :key="g.id"
        class="flex items-center justify-between bg-stone-800/60 border border-stone-600 rounded-xl px-4 py-3"
      >
        <div>
          <span class="font-medium text-stone-200">{{ g.name }}</span>
          <span class="text-stone-500 text-sm ml-2">#{{ g.code }}</span>
          <span
            class="ml-2 text-xs px-2 py-0.5 rounded"
            :class="
              g.status === 'lobby'
                ? 'bg-emerald-900/50 text-emerald-300'
                : g.status === 'in_progress'
                  ? 'bg-amber-900/50 text-amber-300'
                  : 'bg-stone-600 text-stone-400'
            "
          >
            {{ g.status }}
          </span>
        </div>
        <router-link
          :to="`/games/${g.id}`"
          class="text-amber-400 hover:text-amber-300 text-sm font-medium"
        >
          Open →
        </router-link>
      </li>
    </ul>
    <p v-if="games.length === 0 && !loading" class="text-stone-500">No games yet. Create one or join with a code.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';
import { useAuth } from '../composables/useAuth';

const { user } = useAuth();
const games = ref([]);
const loading = ref(true);
const joinCode = ref('');
const joinError = ref('');

onMounted(async () => {
  try {
    const { data } = await api.get('/games');
    games.value = data.data ?? data;
  } finally {
    loading.value = false;
  }
});

async function createGame() {
  try {
    const { data } = await api.post('/games', {});
    games.value = [data, ...(games.value || [])];
    window.location.href = `/games/${data.id}`;
  } catch (e) {
    joinError.value = e.response?.data?.message ?? 'Failed to create game.';
  }
}

async function joinGame() {
  joinError.value = '';
  const code = joinCode.value?.trim().toUpperCase();
  if (!code || code.length !== 6) {
    joinError.value = 'Enter a 6-character game code.';
    return;
  }
  try {
    const { data } = await api.post('/games/join', { code });
    games.value = [data, ...(games.value || []).filter((g) => g.id !== data.id)];
    window.location.href = `/games/${data.id}`;
  } catch (e) {
    joinError.value = e.response?.data?.message ?? 'Could not join game.';
  }
}
</script>
