<script setup>
import { onMounted, ref } from 'vue';
import { fetchRooms } from '../api.js';

const rooms = ref([]);
const isLoading = ref(true);
const errorMessage = ref('');

const loadRooms = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const data = await fetchRooms();
    rooms.value = Array.isArray(data) ? data : [];
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Failed to load rooms';
  } finally {
    isLoading.value = false;
  }
};

onMounted(loadRooms);
</script>

<template>
  <section class="rooms-view">
    <header class="rooms-header">
      <div>
        <p class="eyebrow">Rooms</p>
        <h1>Lista pokoi</h1>
      </div>
    </header>

    <p v-if="isLoading" class="status">Ładowanie danych...</p>
    <p v-else-if="errorMessage" class="status status-error">{{ errorMessage }}</p>

    <ul v-else class="rooms-list">
      <li v-for="room in rooms" :key="room.id ?? `${room.name}-${room.capacity}`" class="room-item">
        <span class="room-name">{{ room.name }}</span>
        <span class="room-capacity">Pojemność: {{ room.capacity }}</span>
      </li>
    </ul>

    <p v-if="!isLoading && !errorMessage && rooms.length === 0" class="status">Brak pokoi do wyświetlenia.</p>
  </section>
</template>
