<script setup>
import { onMounted, ref } from 'vue';
import { ApiError, ensureCsrfCookie, loginUser } from '../api.js';
import FormValidation from '../components/FormValidation.vue';

const emit = defineEmits(['success']);

const title = 'Logowanie';
const eyebrow = 'Logowanie';
const submitLabel = 'Zaloguj się';

const form = ref({
  email: '',
  password: '',
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const fieldErrors = ref({});

const resetMessages = () => {
  errorMessage.value = '';
  fieldErrors.value = {};
};

onMounted(() => {
  void ensureCsrfCookie();
});

const hasFieldError = (fieldName) => {
  const value = fieldErrors.value[fieldName];

  if (Array.isArray(value)) {
    return value.length > 0;
  }

  return Boolean(value);
};

const submitForm = async () => {
  isSubmitting.value = true;
  resetMessages();

  try {
    await ensureCsrfCookie();

    const response = await loginUser({
      email: form.value.email,
      password: form.value.password,
    });

    form.value.email = '';
    form.value.password = '';
    emit('success', response?.user ?? null);
  } catch (error) {
    if (error instanceof ApiError && error.status === 422) {
      const body = typeof error.body === 'object' && error.body !== null ? error.body : null;

      if (body && 'errors' in body) {
        fieldErrors.value = body.errors ?? {};

        if (Object.keys(fieldErrors.value).length === 0) {
          errorMessage.value = error.message;
        }

        return;
      }
    }

    errorMessage.value = error instanceof Error ? error.message : 'Nie udało się wykonać operacji.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <section class="auth-view">
    <header class="section-header">
      <p class="eyebrow">{{ eyebrow }}</p>
      <h1>{{ title }}</h1>
    </header>

    <p v-if="errorMessage" class="status status-error">{{ errorMessage }}</p>

    <form class="auth-form" @submit.prevent="submitForm">
      <label class="field">
        <span>E-mail</span>
        <input
          v-model="form.email"
          type="email"
          autocomplete="email"
          required
          :aria-invalid="hasFieldError('email')"
          :data-status="hasFieldError('email') ? 'error' : undefined"
          :aria-describedby="hasFieldError('email') ? 'email-errors' : undefined"
        />
        <FormValidation field="email" :errors="fieldErrors" />
      </label>

      <label class="field">
        <span>Hasło</span>
        <input
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          required
          minlength="8"
          :aria-invalid="hasFieldError('password')"
          :data-status="hasFieldError('password') ? 'error' : undefined"
          :aria-describedby="hasFieldError('password') ? 'password-errors' : undefined"
        />
        <FormValidation field="password" :errors="fieldErrors" />
      </label>

      <p>Nie masz konta? <a href="/register">Zarejestruj się</a></p>

      <button type="submit" class="submit-button" :disabled="isSubmitting">
        {{ isSubmitting ? 'Wysyłanie...' : submitLabel }}
      </button>
    </form>
  </section>
</template>
