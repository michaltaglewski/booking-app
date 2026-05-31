<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { ApiError, createBooking, ensureCsrfCookie, fetchRooms } from '../api.js';
import QuantityInput from '../components/QuantityInput.vue';
import FormValidation from '../components/FormValidation.vue';

const formatDateInputValue = (date) => {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const todayDate = formatDateInputValue(new Date());

const rooms = ref([]);
const isLoading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const fieldErrors = ref({});
const roomRequestId = ref(0);
let activeAbortController = null;

const form = ref({
  roomId: '',
  startDate: '',
  endDate: '',
  participants: 1,
});

const selectedRoomCapacity = computed(() => {
  const selectedRoom = rooms.value.find((room) => String(room.id ?? room.name) === String(form.value.roomId));

  return selectedRoom?.capacity ?? Number.POSITIVE_INFINITY;
});

const loadRooms = async () => {
  const requestId = ++roomRequestId.value;
  activeAbortController?.abort();
  activeAbortController = new AbortController();
  isLoading.value = true;
  errorMessage.value = '';
  fieldErrors.value = {};
  rooms.value = [];

  try {
    const data = await fetchRooms({
      startsAt: form.value.startDate || undefined,
      endsAt: form.value.endDate || undefined,
      signal: activeAbortController.signal,
    });

    if (requestId !== roomRequestId.value) {
      return;
    }

    rooms.value = Array.isArray(data) ? data : [];

    if (!rooms.value.some((room) => String(room.id ?? room.name) === String(form.value.roomId))) {
      form.value.roomId = '';
    }
  } catch (error) {
    if (requestId !== roomRequestId.value) {
      return;
    }

    if (error instanceof ApiError && error.status === 422) {
      fieldErrors.value = typeof error.body === 'object' && error.body !== null && 'errors' in error.body
        ? error.body.errors ?? {}
        : {};

      if (Object.keys(fieldErrors.value).length === 0) {
        errorMessage.value = error.message;
      }

      return;
    }

    errorMessage.value = error instanceof Error ? error.message : 'Nie udało się załadować pokoi';
  } finally {
    if (requestId !== roomRequestId.value) {
      return;
    }

    isLoading.value = false;
  }
};

const submitForm = async () => {
  const payload = {
    roomId: Number(form.value.roomId),
    startsAt: form.value.startDate,
    endsAt: form.value.endDate,
    participantsCount: Number(form.value.participants),
  };

  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  fieldErrors.value = {};

  try {
    await createBooking(payload);
    successMessage.value = 'The reservation has been created.';
    form.value.roomId = '';
    form.value.startDate = '';
    form.value.endDate = '';
    form.value.participants = 1;
    await loadRooms();
  } catch (error) {
    if (error instanceof ApiError && error.status === 422) {
      fieldErrors.value = typeof error.body === 'object' && error.body !== null && 'errors' in error.body
        ? error.body.errors ?? {}
        : {};

      if (Object.keys(fieldErrors.value).length === 0) {
        errorMessage.value = error.message;
      }

      return;
    }

    errorMessage.value = error instanceof Error ? error.message : 'Failed to create booking';
  } finally {
    isSubmitting.value = false;
  }
};

const roomFieldHasError = (fieldName) => {
  const value = fieldErrors.value[fieldName];

  if (Array.isArray(value)) {
    return value.length > 0;
  }

  return Boolean(value);
};

onMounted(() => {
  void ensureCsrfCookie();
  void loadRooms();
});

onBeforeUnmount(() => {
  activeAbortController?.abort();
});

watch(
  () => [form.value.startDate, form.value.endDate],
  ([startDate, endDate]) => {
    if (startDate && endDate && endDate < startDate) {
      form.value.endDate = startDate;
      return;
    }

    loadRooms();
  },
);
</script>

<template>
  <section class="reservation-view">
    <header class="section-header">
      <p class="eyebrow">Reservations</p>
      <h1>Formularz rezerwacji</h1>
    </header>

    <p v-if="errorMessage" class="status status-error">{{ errorMessage }}</p>

    <form class="reservation-form" @submit.prevent="submitForm">
      <label class="field">
        <span>Data rozpoczęcia</span>
        <input
          v-model="form.startDate"
          type="date"
          :min="todayDate"
          required
          :aria-invalid="roomFieldHasError('starts_at')"
          :data-status="roomFieldHasError('starts_at') ? 'error' : undefined"
          :aria-describedby="roomFieldHasError('starts_at') ? 'starts_at-errors' : undefined"
        />
        <FormValidation field="starts_at" :errors="fieldErrors" />
      </label>

      <label class="field">
        <span>Data zakończenia</span>
        <input
          v-model="form.endDate"
          type="date"
          :min="form.startDate || todayDate"
          required
          :aria-invalid="roomFieldHasError('ends_at')"
          :data-status="roomFieldHasError('ends_at') ? 'error' : undefined"
          :aria-describedby="roomFieldHasError('ends_at') ? 'ends_at-errors' : undefined"
        />
        <FormValidation field="ends_at" :errors="fieldErrors" />
      </label>

      <label class="field">
        <span>Pokój</span>
        <div class="select-shell">
          <select
              v-model="form.roomId"
              :disabled="isLoading || !!errorMessage || rooms.length === 0"
              :aria-busy="isLoading"
              :aria-invalid="roomFieldHasError('room_id')"
              :data-status="roomFieldHasError('room_id') ? 'error' : undefined"
              :aria-describedby="roomFieldHasError('room_id') ? 'room_id-errors' : undefined"
              required
          >
            <option value="" disabled>
              {{ isLoading ? 'Ładowanie pokoi...' : 'Wybierz pokój' }}
            </option>
            <option
                v-for="room in rooms"
                :key="room.id ?? `${room.name}-${room.capacity}`"
                :value="room.id ?? room.name"
            >
              {{ room.name }} ({{ room.capacity }})
            </option>
          </select>
          <span v-if="isLoading" class="inline-loader" aria-hidden="true"></span>
        </div>
        <FormValidation field="room_id" :errors="fieldErrors" />
      </label>

      <label class="field">
        <span>Liczba uczestników</span>
        <QuantityInput v-model="form.participants" :min="1" :max="selectedRoomCapacity" />
        <FormValidation field="participants_count" :errors="fieldErrors" />
      </label>

      <button
        type="submit"
        class="submit-button"
        :disabled="isLoading || isSubmitting || !!errorMessage || rooms.length === 0"
      >
        {{ isSubmitting ? 'Rezerwuję...' : 'Zarezerwuj' }}
      </button>
    </form>

    <p v-if="successMessage" class="status status-success">{{ successMessage }}</p>

    <div v-if="isLoading" class="loading-backdrop" aria-hidden="true"></div>
  </section>
</template>
