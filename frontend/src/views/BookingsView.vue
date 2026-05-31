<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { ApiError, cancelBooking, fetchBookings } from '../api.js';

const bookings = ref([]);
const isLoading = ref(true);
const errorMessage = ref('');
const actionMessage = ref('');
const isConfirmOpen = ref(false);
const bookingPendingCancel = ref(null);
const activeAbortController = ref(null);
const requestId = ref(0);

const dateFormatter = new Intl.DateTimeFormat('pl-PL', {
  dateStyle: 'medium',
});

const loadBookings = async () => {
  const currentRequestId = ++requestId.value;
  activeAbortController.value?.abort();
  activeAbortController.value = new AbortController();
  isLoading.value = true;
  errorMessage.value = '';
  actionMessage.value = '';

  try {
    const data = await fetchBookings(activeAbortController.value.signal);

    if (currentRequestId !== requestId.value) {
      return;
    }

    bookings.value = Array.isArray(data) ? data : [];
  } catch (error) {
    if (currentRequestId !== requestId.value) {
      return;
    }

    errorMessage.value = error instanceof Error ? error.message : 'Nie udało się załadować rezerwacji';
    bookings.value = [];
  } finally {
    if (currentRequestId === requestId.value) {
      isLoading.value = false;
    }
  }
};

const formatDate = (value) => {
  if (!value) {
    return '—';
  }

  const date = typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)
    ? new Date(`${value}T00:00:00`)
    : new Date(value);

  if (Number.isNaN(date.getTime())) {
    return '—';
  }

  return dateFormatter.format(date);
};

const getRoomName = (booking) => booking?.room?.name ?? 'Nieznany pokój';

const getStatusLabel = (status) => {
  switch (status) {
    case 'confirmed':
      return 'Zatwierdzone';
    case 'cancelled':
      return 'Anulowane';
    default:
      return 'Oczekujące na potwierdzenie';
  }
};

const cancelReservation = async (booking) => {
  if (!booking?.id || booking.status === 'cancelled') {
    return;
  }

  bookingPendingCancel.value = booking;
  isConfirmOpen.value = true;
};

const closeConfirmDialog = () => {
  isConfirmOpen.value = false;
  bookingPendingCancel.value = null;
};

const confirmCancellation = async () => {
  const booking = bookingPendingCancel.value;

  if (!booking?.id) {
    closeConfirmDialog();
    return;
  }

  errorMessage.value = '';

  try {
    await cancelBooking(booking.id);
    await loadBookings();
    actionMessage.value = 'Rezerwacja została anulowana.';
  } catch (error) {
    if (error instanceof ApiError && error.status === 403) {
      errorMessage.value = 'Brak uprawnień do anulowania tej rezerwacji.';
    } else {
      errorMessage.value = error instanceof Error ? error.message : 'Nie udało się anulować rezerwacji';
    }
  } finally {
    closeConfirmDialog();
  }
};

onMounted(() => {
  void loadBookings();
});

onBeforeUnmount(() => {
  activeAbortController.value?.abort();
});
</script>

<template>
  <section class="bookings-view">
    <header class="section-header">
      <h1>Lista rezerwacji</h1>
    </header>

    <p v-if="errorMessage" class="status status-error">{{ errorMessage }}</p>
    <p v-else-if="actionMessage" class="status status-success">{{ actionMessage }}</p>

    <p v-if="isLoading" class="status">Ładowanie danych...</p>
    <p v-else-if="!errorMessage && bookings.length === 0" class="status">Brak rezerwacji do wyświetlenia.</p>

    <div v-else class="table-shell">
      <table class="bookings-table">
        <thead>
          <tr>
            <th scope="col">Nazwa pokoju</th>
            <th scope="col">Data rozpoczęcia</th>
            <th scope="col">Data zakończenia</th>
            <th scope="col">Liczba uczestników</th>
            <th scope="col">Status</th>
            <th scope="col">Data utworzenia</th>
            <th scope="col">Akcje</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="booking in bookings" :key="booking.id">
            <td>{{ getRoomName(booking) }}</td>
            <td>{{ formatDate(booking.starts_at) }}</td>
            <td>{{ formatDate(booking.ends_at) }}</td>
            <td>{{ booking.participants_count }}</td>
            <td>
              <span class="booking-status" :data-status="booking.status">
                {{ getStatusLabel(booking.status) }}
              </span>
            </td>
            <td>{{ formatDate(booking.created_at) }}</td>
            <td>
              <button
                type="button"
                class="booking-action-button"
                :disabled="booking.status === 'cancelled'"
                @click="cancelReservation(booking)"
              >
                Anuluj
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isConfirmOpen" class="confirm-overlay" role="presentation">
      <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="cancel-booking-title">
        <h2 id="cancel-booking-title">Potwierdzenie anulowania</h2>
        <p>Czy na pewno chcesz anulować tą rezerwację?</p>

        <div class="confirm-actions">
          <button type="button" class="confirm-button confirm-button-secondary" @click="closeConfirmDialog">
            Nie
          </button>
          <button type="button" class="confirm-button confirm-button-danger" @click="confirmCancellation">
            Tak, anuluj
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
