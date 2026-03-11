<template>
  <div
    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-stone-700 text-stone-100 text-sm font-medium border border-stone-600 cursor-grab active:cursor-grabbing select-none"
    draggable="true"
    @dragstart="onDragStart"
  >
    {{ tile.word }}
  </div>
</template>

<script setup>
const props = defineProps({
  tile: { type: Object, required: true },
});

function onDragStart(e) {
  e.dataTransfer.setData('application/json', JSON.stringify(props.tile));
  e.dataTransfer.effectAllowed = 'move';
}
</script>
