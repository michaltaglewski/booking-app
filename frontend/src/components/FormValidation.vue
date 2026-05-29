<script setup>
import { computed } from 'vue';

const props = defineProps({
  field: {
    type: String,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const messages = computed(() => {
  const value = props.errors?.[props.field];

  if (Array.isArray(value)) {
    return value.filter(Boolean);
  }

  if (typeof value === 'string' && value.trim() !== '') {
    return [value];
  }

  return [];
});
</script>

<template>
  <div v-if="messages.length" class="form-validation" :id="`${field}-errors`" role="alert">
    <p v-for="(message, index) in messages" :key="`${field}-${index}-${message}`">
      {{ message }}
    </p>
  </div>
</template>
