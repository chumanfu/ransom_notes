<template>
  <header class="border-b border-stone-700 bg-stone-900/80 backdrop-blur">
    <div class="container mx-auto px-4 flex items-center justify-between h-14">
      <router-link to="/" class="text-xl font-bold tracking-tight text-amber-400">
        Ransom Notes
      </router-link>
      <nav class="flex items-center gap-4">
        <router-link
          v-if="user?.is_admin"
          to="/admin/cards"
          class="text-stone-400 hover:text-amber-400 transition"
        >
          Cards
        </router-link>
        <span class="text-stone-500 text-sm">{{ user?.name }}</span>
        <button
          type="button"
          class="text-stone-400 hover:text-red-400 transition text-sm"
          @click="$emit('logout')"
        >
          Log out
        </button>
      </nav>
    </div>
  </header>
</template>

<script setup>
defineProps({ user: Object });
defineEmits(['logout']);
</script>
