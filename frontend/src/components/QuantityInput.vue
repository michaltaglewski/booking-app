<script setup>
const props = defineProps({
  modelValue: {
    type: Number,
    required: true,
  },
  min: {
    type: Number,
    default: 1,
  },
});

const emit = defineEmits(['update:modelValue']);

const decrease = () => {
  emit('update:modelValue', Math.max(props.min, props.modelValue - 1));
};

const increase = () => {
  emit('update:modelValue', props.modelValue + 1);
};

const onInput = (event) => {
  const value = Number.parseInt(event.target.value, 10);

  if (Number.isNaN(value)) {
    emit('update:modelValue', props.min);
    return;
  }

  emit('update:modelValue', Math.max(props.min, value));
};
</script>

<template>
  <div class="quantity-input">
    <button type="button" class="quantity-button" @click="decrease" :disabled="modelValue <= min">
      -
    </button>

    <input
      class="quantity-field"
      type="number"
      :min="min"
      :value="modelValue"
      @input="onInput"
    />

    <button type="button" class="quantity-button" @click="increase">+</button>
  </div>
</template>
