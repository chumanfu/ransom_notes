<template>
  <div
    class="tile-plate min-h-[120px] rounded-xl border-2 border-dashed border-stone-600 bg-stone-900/50 p-4 flex flex-wrap items-start content-start gap-2"
    :class="{ 'border-amber-500': isDragging }"
    @dragover.prevent="isDragging = true"
    @dragleave="isDragging = false"
    @drop.prevent="onDrop"
  >
    <template v-for="(tile, index) in modelValue" :key="tile.id">
      <div
        class="group relative inline-flex items-center px-3 py-1.5 rounded-lg bg-stone-700 text-stone-100 text-sm font-medium border border-stone-600 select-none"
      >
        <span>{{ tile.word }}</span>
        <button
          type="button"
          class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-red-500 hover:bg-red-600 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow"
          aria-label="Remove"
          @click.stop="remove(index)"
        >
          ×
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  maxTiles: { type: Number, default: 30 },
});

const emit = defineEmits(['update:modelValue']);

const isDragging = ref(false);

function remove(index) {
  const list = [...(props.modelValue ?? [])];
  list.splice(index, 1);
  emit('update:modelValue', list);
}

function onDrop(e) {
  isDragging.value = false;
  const payload = e.dataTransfer.getData('application/json');
  if (!payload) return;
  try {
    const tile = JSON.parse(payload);
    const current = props.modelValue ?? [];
    if (current.length >= (props.maxTiles ?? 30)) return;
    if (current.some((t) => t.id === tile.id)) return;
    emit('update:modelValue', [...current, tile]);
  } catch (_) {}
}
</script>
