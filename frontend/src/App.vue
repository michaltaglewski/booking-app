<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { UserRound } from 'lucide-vue-next';
import { ApiError, fetchCurrentUser, logoutUser } from './api.js';
import { ROUTES, ROUTING, getRouteConfig, resolveRoute } from './routing.js';
import LoginView from './views/LoginView.vue';
import RegisterView from './views/RegisterView.vue';
import BookingsView from './views/BookingsView.vue';
import ReservationView from './views/ReservationView.vue';
import RoomsView from './views/RoomsView.vue';

const pathname = ref(window.location.pathname);
const route = computed(() => resolveRoute(pathname.value));
const routeConfig = computed(() => getRouteConfig(route.value));
const isResolvingRoute = ref(false);
const routeIsReady = ref(false);
const currentUser = ref(null);
const authStatus = ref('loading');
const isUserMenuOpen = ref(false);
const userMenuRef = ref(null);
let currentUserRequest = null;
let authRequestId = 0;

const syncPathname = () => {
  pathname.value = window.location.pathname;
};

const updatePathname = (path, replace = false) => {
  if (window.location.pathname !== path) {
    window.history[replace ? 'replaceState' : 'pushState']({}, '', path);
    syncPathname();
  }
};

const navigateTo = (path) => {
  updatePathname(path);
};

const redirectTo = (path) => {
  updatePathname(path, true);
};

const closeUserMenu = () => {
  isUserMenuOpen.value = false;
};

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const handleDocumentClick = (event) => {
  if (!isUserMenuOpen.value) {
    return;
  }

  if (userMenuRef.value?.contains(event.target)) {
    return;
  }

  closeUserMenu();
};

const handleDocumentKeydown = (event) => {
  if (event.key === 'Escape') {
    closeUserMenu();
  }
};

const requiresAuthCheck = (config) => Boolean(config?.auth?.requiresUser || config?.auth?.guestOnly);

const resolveCurrentUser = async () => {
  if (authStatus.value !== 'loading') {
    return currentUser.value;
  }

  if (!currentUserRequest) {
    currentUserRequest = (async () => {
      try {
        const user = await fetchCurrentUser();
        currentUser.value = user;
        authStatus.value = 'authenticated';
        return user;
      } catch (error) {
        if (error instanceof ApiError && (error.status === 401 || error.status === 403)) {
          currentUser.value = null;
          authStatus.value = 'unauthenticated';
          return null;
        }

        currentUser.value = null;
        authStatus.value = 'unauthenticated';
        return null;
      } finally {
        currentUserRequest = null;
      }
    })();
  }

  return currentUserRequest;
};

const evaluateRouteAccess = async () => {
  const config = routeConfig.value;
  const authConfig = config?.auth;
  const requestId = ++authRequestId;

  if (!requiresAuthCheck(config)) {
    routeIsReady.value = true;
    isResolvingRoute.value = false;
    return;
  }

  isResolvingRoute.value = true;
  routeIsReady.value = false;

  try {
    await resolveCurrentUser();

    if (requestId !== authRequestId) {
      return;
    }

    if (authConfig?.guestOnly && authStatus.value === 'authenticated') {
      redirectTo(ROUTING.defaultRoute);
      return;
    }

    if (authConfig?.requiresUser && authStatus.value !== 'authenticated') {
      redirectTo(ROUTING.guestRedirectTo);
      return;
    }

    routeIsReady.value = true;
  } catch (error) {
    if (requestId !== authRequestId) {
      return;
    }

    routeIsReady.value = true;
  } finally {
    if (requestId === authRequestId) {
      isResolvingRoute.value = false;
    }
  }
};

const handleAuthSuccess = (user) => {
  currentUser.value = user;
  authStatus.value = 'authenticated';
  closeUserMenu();

  navigateTo(ROUTING.defaultRoute);
};

const handleLogout = async () => {
  await logoutUser();
  currentUser.value = null;
  authStatus.value = 'unauthenticated';
  closeUserMenu();
  navigateTo(ROUTING.guestRedirectTo);
};

onMounted(() => {
  window.addEventListener('popstate', syncPathname);
  document.addEventListener('click', handleDocumentClick);
  document.addEventListener('keydown', handleDocumentKeydown);

  if (window.location.pathname === '/') {
    window.history.replaceState({}, '', ROUTING.defaultRoute);
    syncPathname();
  }

  void resolveCurrentUser();
});

onBeforeUnmount(() => {
  window.removeEventListener('popstate', syncPathname);
  document.removeEventListener('click', handleDocumentClick);
  document.removeEventListener('keydown', handleDocumentKeydown);
});

watch(route, () => {
  routeIsReady.value = false;
  void evaluateRouteAccess();
}, { immediate: true });
</script>

<template>
  <main class="app-shell">
    <header class="topbar">
      <button type="button" class="brand-link" @click="navigateTo(ROUTING.defaultRoute)">
        Booking App
      </button>

      <div class="topbar-actions">
        <template v-if="authStatus === 'authenticated' && currentUser">
          <div ref="userMenuRef" class="user-menu">
            <button
              type="button"
              class="topbar-button user-menu-trigger"
              :aria-expanded="isUserMenuOpen"
              aria-haspopup="menu"
              @click="toggleUserMenu"
            >
              <UserRound aria-hidden="true" class="user-menu-icon" />
              <span class="user-name">{{ currentUser.name }}</span>
              <span aria-hidden="true" class="user-menu-caret">▾</span>
            </button>

            <div v-if="isUserMenuOpen" class="user-menu-panel" role="menu" aria-label="Menu użytkownika">
              <button type="button" class="user-menu-item" role="menuitem" @click="handleLogout">
                Wyloguj
              </button>
            </div>
          </div>
        </template>

        <template v-else-if="authStatus === 'unauthenticated'">
          <button type="button" class="topbar-link" :data-active="route === 'login'" @click="navigateTo(ROUTING.guestRedirectTo)">
            Zaloguj
          </button>
          <button type="button" class="topbar-link" :data-active="route === 'register'" @click="navigateTo(ROUTES.register.path)">
            Zarejestruj
          </button>
        </template>
      </div>
    </header>

    <nav class="app-nav" aria-label="Główna nawigacja">
      <button type="button" class="nav-button" :data-active="route === 'rooms'" @click="navigateTo(ROUTING.defaultRoute)">
        Pokoje
      </button>
      <button
        type="button"
        class="nav-button"
        :data-active="route === 'reservations'"
        @click="navigateTo(ROUTES.reservations.path)"
      >
        Zarezerwuj
      </button>
      <button
        type="button"
        class="nav-button"
        :data-active="route === 'bookings'"
        @click="navigateTo(ROUTES.bookings.path)"
      >
        Twoje rezerwacje
      </button>
    </nav>

    <p v-if="isResolvingRoute" class="status">Autoryzacja...</p>

    <RoomsView v-else-if="routeIsReady && route === 'rooms'" />
    <ReservationView v-else-if="routeIsReady && route === 'reservations'" />
    <BookingsView v-else-if="routeIsReady && route === 'bookings'" />
    <LoginView v-else-if="routeIsReady && route === 'login'" @success="handleAuthSuccess" />
    <RegisterView v-else-if="routeIsReady && route === 'register'" @success="handleAuthSuccess" />

    <section v-else-if="routeIsReady" class="not-found">
      <h1>Booking App</h1>
      <h2><strong>404</strong></h2>
      <p>Strona nie istnieje.</p>
    </section>
  </main>
</template>
