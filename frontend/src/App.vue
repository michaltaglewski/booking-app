<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { resolveRoute } from './routing.js';
import RoomsView from './views/RoomsView.vue';

const pathname = ref(window.location.pathname);
const route = computed(() => resolveRoute(pathname.value));

const syncPathname = () => {
  pathname.value = window.location.pathname;
};

onMounted(() => {
  window.addEventListener('popstate', syncPathname);

  if (window.location.pathname === '/') {
    window.history.replaceState({}, '', '/rooms');
    syncPathname();
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('popstate', syncPathname);
});
</script>

<template>
  <main class="app-shell">
    <RoomsView v-if="route === 'rooms'" />

    <section v-else class="not-found">
      <h1>Booking App</h1>
      <h2><strong>404</strong></h2>
      <p>Strona nie istnieje.</p>
    </section>
  </main>
</template>
