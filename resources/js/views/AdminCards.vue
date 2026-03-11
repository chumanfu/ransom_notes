<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-stone-100">Prompt cards</h1>
      <router-link to="/" class="text-amber-400 hover:text-amber-300 text-sm">← Games</router-link>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
      <div class="bg-stone-800/60 rounded-xl p-6 border border-stone-600">
        <h2 class="text-lg font-semibold text-stone-200 mb-4">Add card</h2>
        <textarea
          v-model="newText"
          rows="3"
          placeholder="Prompt text..."
          class="w-full rounded-lg bg-stone-900 border border-stone-600 px-4 py-2 text-stone-100 placeholder:text-stone-500 focus:border-amber-500 outline-none"
        />
        <button
          type="button"
          class="mt-3 rounded-lg bg-amber-500 hover:bg-amber-600 text-stone-950 font-semibold px-4 py-2 disabled:opacity-50"
          :disabled="!newText.trim() || saving"
          @click="addCard"
        >
          {{ saving ? 'Adding…' : 'Add' }}
        </button>
      </div>
      <div class="bg-stone-800/60 rounded-xl p-6 border border-stone-600">
        <h2 class="text-lg font-semibold text-stone-200 mb-4">Import from file</h2>
        <p class="text-stone-500 text-sm mb-2">One prompt per line (txt or csv)</p>
        <input
          ref="fileInput"
          type="file"
          accept=".txt,.csv"
          class="block w-full text-sm text-stone-400 file:mr-4 file:rounded file:border-0 file:bg-amber-500 file:px-4 file:py-2 file:text-stone-950 file:font-semibold hover:file:bg-amber-600"
          @change="onFileSelect"
        />
        <button
          type="button"
          class="mt-3 rounded-lg bg-stone-600 hover:bg-stone-500 text-stone-100 font-medium px-4 py-2 disabled:opacity-50"
          :disabled="!selectedFile || importing"
          @click="importFile"
        >
          {{ importing ? 'Importing…' : 'Import' }}
        </button>
        <p v-if="importResult" class="mt-2 text-sm text-emerald-400">{{ importResult }}</p>
      </div>
    </div>
    <div class="mt-8">
      <h2 class="text-lg font-semibold text-stone-200 mb-4">Existing cards</h2>
      <ul class="space-y-2">
        <li
          v-for="card in cards"
          :key="card.id"
          class="flex items-center justify-between bg-stone-800/60 rounded-lg px-4 py-3 border border-stone-600"
        >
          <p class="text-stone-200 flex-1 truncate">{{ card.text }}</p>
          <button
            type="button"
            class="ml-2 text-red-400 hover:text-red-300 text-sm"
            @click="deleteCard(card)"
          >
            Delete
          </button>
        </li>
      </ul>
      <p v-if="cards.length === 0 && !loading" class="text-stone-500">No cards yet.</p>
    </div>
    <div class="mt-10 pt-8 border-t border-stone-700">
      <h2 class="text-lg font-semibold text-stone-200 mb-2">Danger zone</h2>
      <p class="text-stone-500 text-sm mb-3">Wipe the database, re-run migrations, and re-seed. All users and data are removed; you will need to log in again (e.g. admin@example.com).</p>
      <button
        type="button"
        class="rounded-lg bg-red-900/60 hover:bg-red-800/60 text-red-200 font-medium px-4 py-2 border border-red-700/60 disabled:opacity-50"
        :disabled="resetting"
        @click="resetDatabase"
      >
        {{ resetting ? 'Resetting…' : 'Reset database' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const cards = ref([]);
const newText = ref('');
const saving = ref(false);
const loading = ref(true);
const selectedFile = ref(null);
const fileInput = ref(null);
const importing = ref(false);
const importResult = ref('');
const resetting = ref(false);

onMounted(async () => {
  try {
    const { data } = await api.get('/prompt-cards');
    cards.value = data.data ?? data;
  } finally {
    loading.value = false;
  }
});

async function addCard() {
  if (!newText.value.trim()) return;
  saving.value = true;
  try {
    const { data } = await api.post('/prompt-cards', { text: newText.value.trim() });
    cards.value = [data, ...cards.value];
    newText.value = '';
  } finally {
    saving.value = false;
  }
}

function onFileSelect(e) {
  selectedFile.value = e.target.files?.[0] ?? null;
}

async function importFile() {
  if (!selectedFile.value) return;
  importing.value = true;
  importResult.value = '';
  try {
    const form = new FormData();
    form.append('file', selectedFile.value);
    const { data } = await api.post('/prompt-cards/import', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    importResult.value = data.message ?? `Imported ${data.count} cards.`;
    const { data: list } = await api.get('/prompt-cards');
    cards.value = list.data ?? list;
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
  } finally {
    importing.value = false;
  }
}

async function deleteCard(card) {
  if (!confirm('Delete this card?')) return;
  await api.delete(`/prompt-cards/${card.id}`);
  cards.value = cards.value.filter((c) => c.id !== card.id);
}

async function resetDatabase() {
  if (!confirm('Reset the entire database? All games, users, and cards will be deleted. You will be logged out.')) return;
  resetting.value = true;
  try {
    const { data } = await api.post('/admin/database/reset');
    localStorage.removeItem('token');
    alert(data.message ?? 'Database reset. Redirecting to login.');
    window.location.href = '/login';
  } catch (e) {
    alert(e.response?.data?.message ?? 'Reset failed.');
  } finally {
    resetting.value = false;
  }
}
</script>
