<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { resolveRoute } from './routing.js';
import ReservationView from './views/ReservationView.vue';
import RoomsView from './views/RoomsView.vue';

const pathname = ref(window.location.pathname);
const route = computed(() => resolveRoute(pathname.value));

const syncPathname = () => {
  pathname.value = window.location.pathname;
};

const navigateTo = (path) => {
  if (window.location.pathname !== path) {
    window.history.pushState({}, '', path);
    syncPathname();
  }
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
    <nav class="app-nav" aria-label="Główna nawigacja">
      <button type="button" class="nav-button" :data-active="route === 'rooms'" @click="navigateTo('/rooms')">
        Pokoje
      </button>
      <button
        type="button"
        class="nav-button"
        :data-active="route === 'reservations'"
        @click="navigateTo('/reservations')"
      >
        Zarezerwuj
      </button>
    </nav>

    <RoomsView v-if="route === 'rooms'" />
    <ReservationView v-else-if="route === 'reservations'" />

    <section v-else class="not-found">
      <h1>Booking App</h1>
      <h2><strong>404</strong></h2>
      <p>Strona nie istnieje.</p>
    </section>
  </main>
</template>
