<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <router-link to="/" class="text-amber-400 hover:text-amber-300 text-sm">← Back</router-link>
      <div class="text-stone-400 text-sm">Game #{{ state?.game?.code }}</div>
    </div>

    <div v-if="!state" class="text-stone-500">Loading…</div>

    <template v-else>
      <div class="flex flex-wrap gap-2 mb-4">
        <span
          v-for="p in state.players"
          :key="p.id"
          class="px-3 py-1 rounded-full bg-stone-700 text-stone-300 text-sm"
        >
          {{ p.name }} ({{ p.score }})
        </span>
      </div>

      <!-- Lobby -->
      <div v-if="state.game?.status === 'lobby'" class="bg-stone-800/60 rounded-xl p-6 border border-stone-600">
        <p class="text-stone-300 mb-4">Waiting for admin to start the game. Share code <strong class="text-amber-400">{{ state.game.code }}</strong> for others to join.</p>
        <button
          v-if="user?.is_admin"
          type="button"
          class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2"
          :disabled="starting"
          @click="startRound"
        >
          {{ starting ? 'Starting…' : 'Start round' }}
        </button>
      </div>

      <!-- Building / Voting / Completed round -->
      <template v-else-if="state.current_round">
        <div class="bg-stone-800/60 rounded-xl p-6 border border-stone-600 mb-6">
          <p class="text-stone-400 text-sm mb-1">Prompt</p>
          <p class="text-xl text-stone-100 font-medium">{{ state.current_round.prompt_card?.text }}</p>
          <p class="text-stone-500 text-sm mt-2">Phase: {{ state.current_round.status }}</p>
        </div>

        <!-- Building: my tiles and plate -->
        <template v-if="state.current_round.status === 'building'">
          <div class="mb-4 flex items-center gap-4 flex-wrap">
            <button
              type="button"
              class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2 disabled:opacity-50"
              :disabled="tilesCount >= 60 || drawing"
              @click="drawTiles"
            >
              {{ drawing ? 'Drawing…' : 'Get tiles' }} ({{ tilesCount }}/60)
            </button>
          </div>
          <p class="text-stone-400 text-sm mb-2">Your tiles (drag onto plate):</p>
          <div class="flex flex-wrap gap-2 mb-4 min-h-[48px]">
            <DraggableTile
              v-for="t in handTiles"
              :key="t.id"
              :tile="t"
            />
          </div>
          <p class="text-stone-400 text-sm mb-2">Your plate (max 30):</p>
          <TilePlate
            v-model="plateTiles"
            :max-tiles="30"
          />
          <div class="mt-4 flex gap-3">
            <button
              type="button"
              class="rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-4 py-2 disabled:opacity-50"
              :disabled="plateTiles.length === 0 || submitting"
              @click="submitPlate"
            >
              {{ submitting ? 'Saving…' : 'Submit' }}
            </button>
            <button
              v-if="user?.is_admin"
              type="button"
              class="rounded-lg bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2"
              :disabled="stopping"
              @click="stopRound"
            >
              {{ stopping ? 'Stopping…' : 'Stop round' }}
            </button>
          </div>
        </template>

        <!-- Voting -->
        <template v-else-if="state.current_round.status === 'voting'">
          <div class="space-y-4">
            <p class="text-stone-300">Rank each submission: 1 = best, {{ state.current_round.submissions?.length }} = worst.</p>
            <div
              v-for="(sub, i) in state.current_round.submissions"
              :key="sub.id"
              class="bg-stone-800 rounded-lg p-4 border border-stone-600"
            >
              <p class="text-stone-400 text-sm mb-2">{{ sub.user_name }}</p>
              <p class="text-stone-100 font-medium">{{ (sub.words || []).join(' ') || '(empty)' }}</p>
              <label class="mt-2 flex items-center gap-2">
                <span class="text-stone-500 text-sm">Rank:</span>
                <select
                  v-model.number="votes[sub.id]"
                  class="rounded bg-stone-700 border border-stone-600 px-2 py-1 text-stone-100"
                >
                  <option v-for="r in (state.current_round.submissions?.length || 1)" :key="r" :value="r">{{ r }}</option>
                </select>
              </label>
            </div>
            <button
              type="button"
              class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2 disabled:opacity-50"
              :disabled="!votesComplete || submittingVotes"
              @click="submitVotes"
            >
              {{ submittingVotes ? 'Saving…' : 'Submit votes' }}
            </button>
            <button
              v-if="user?.is_admin"
              type="button"
              class="ml-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-4 py-2"
              :disabled="completing"
              @click="completeRound"
            >
              {{ completing ? 'Completing…' : 'Complete round' }}
            </button>
          </div>
        </template>

        <!-- Round just completed: top up -->
        <div v-if="showTopUp" class="bg-stone-800/60 rounded-xl p-6 border border-stone-600">
          <p class="text-stone-300 mb-4">Round over. Top up your tiles before the next round.</p>
          <button
            type="button"
            class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2 disabled:opacity-50"
            :disabled="tilesCount >= 60 || drawing"
            @click="topUpTiles"
          >
            {{ drawing ? 'Drawing…' : 'Top up tiles' }} ({{ tilesCount }}/60)
          </button>
        </div>

        <!-- Next round (admin) -->
        <div v-if="user?.is_admin && showTopUp && state.game?.status === 'in_progress'" class="mt-4">
          <button
            type="button"
            class="rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2"
            :disabled="starting"
            @click="startRound"
          >
            {{ starting ? 'Starting…' : 'Start next round' }}
          </button>
        </div>
      </template>

      <div v-else-if="state.game?.status === 'completed'" class="text-center py-8 text-amber-400 text-xl">
        Game over! Check scores above.
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useAuth } from '../composables/useAuth';
import TilePlate from '../components/TilePlate.vue';
import DraggableTile from '../components/DraggableTile.vue';

const route = useRoute();
const { user } = useAuth();
const gameId = computed(() => route.params.id);

const state = ref(null);
const allTiles = ref([]);
const plateTiles = ref([]);
const handTiles = computed(() => {
  const onPlate = new Set((plateTiles.value || []).map((t) => t.id));
  return (allTiles.value || []).filter((t) => !onPlate.has(t.id));
});
const votes = ref({});
const loading = ref(true);
const drawing = ref(false);
const submitting = ref(false);
const submittingVotes = ref(false);
const stopping = ref(false);
const starting = ref(false);
const completing = ref(false);

const tilesCount = computed(() => (allTiles.value || []).length);

const showTopUp = computed(() => {
  const r = state.value?.current_round;
  if (!r) return false;
  return r.status === 'completed';
});

const votesComplete = computed(() => {
  const subs = state.value?.current_round?.submissions ?? [];
  if (subs.length === 0) return false;
  return subs.every((s) => votes.value[s.id] >= 1);
});

function fetchState() {
  return api.get(`/games/${gameId.value}/state`).then(({ data }) => {
    state.value = data;
  });
}

function fetchTiles() {
  return api.get(`/games/${gameId.value}/tiles`).then(({ data }) => {
    allTiles.value = data.tiles ?? [];
  });
}

let pollTimer;
function startPolling() {
  pollTimer = setInterval(() => {
    fetchState();
    if (state.value?.current_round?.status === 'building') {
      fetchTiles();
    }
  }, 3000);
}

onMounted(async () => {
  try {
    await fetchState();
    await fetchTiles();
    startPolling();
    const subs = state.value?.current_round?.submissions ?? [];
    subs.forEach((s) => { votes.value[s.id] = 1; });
  } catch (e) {
    state.value = null;
  } finally {
    loading.value = false;
  }
});

watch(
  () => state.value?.current_round?.submissions,
  (subs) => {
    if (subs?.length) {
      subs.forEach((s) => {
        if (votes.value[s.id] == null) votes.value[s.id] = 1;
      });
    }
  },
  { deep: true }
);

async function drawTiles() {
  drawing.value = true;
  try {
    const { data } = await api.post(`/games/${gameId.value}/tiles/draw`);
    allTiles.value = [...(allTiles.value || []), ...(data.drawn || [])];
  } finally {
    drawing.value = false;
  }
}

async function topUpTiles() {
  drawing.value = true;
  try {
    const { data } = await api.post(`/games/${gameId.value}/tiles/top-up`);
    allTiles.value = [...(allTiles.value || []), ...(data.drawn || [])];
  } finally {
    drawing.value = false;
  }
}

async function submitPlate() {
  if (plateTiles.value.length === 0) return;
  submitting.value = true;
  try {
    await api.post(`/games/${gameId.value}/rounds/${state.value.current_round.id}/submission`, {
      tile_order: plateTiles.value.map((t) => t.id),
    });
    await fetchState();
  } finally {
    submitting.value = false;
  }
}

async function submitVotes() {
  submittingVotes.value = true;
  try {
    await api.post(`/games/${gameId.value}/rounds/${state.value.current_round.id}/votes`, {
      votes: Object.entries(votes.value).map(([round_submission_id, rank]) => ({
        round_submission_id: Number(round_submission_id),
        rank,
      })),
    });
    await fetchState();
  } finally {
    submittingVotes.value = false;
  }
}

async function stopRound() {
  stopping.value = true;
  try {
    await api.post(`/games/${gameId.value}/rounds/stop`);
    await fetchState();
  } finally {
    stopping.value = false;
  }
}

async function startRound() {
  starting.value = true;
  try {
    await api.post(`/games/${gameId.value}/rounds/start`);
    await fetchState();
    await fetchTiles();
    plateTiles.value = [];
  } finally {
    starting.value = false;
  }
}

async function completeRound() {
  completing.value = true;
  try {
    await api.post(`/games/${gameId.value}/rounds/complete`);
    await fetchState();
  } finally {
    completing.value = false;
  }
}
</script>
