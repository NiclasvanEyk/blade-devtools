<script setup lang="ts">
const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ (e: 'update:modelValue', payload: boolean) }>()

const toggle = () => emit('update:modelValue', !props.modelValue)
</script>

<template>
  <label
    class="container"
    for="blade-devtools-toggle"
    tabindex="1"
    @keydown.self.enter.prevent="toggle()"
    @keydown.self.space.prevent="toggle()"
  >
    B

    <input
      :checked="modelValue"
      @input="$emit('update:modelValue', $event.target.checked)"
      id="blade-devtools-toggle"
      type="checkbox"
      class="toggle"
      tabindex="-1"
    />
  </label>
</template>

<style scoped>
.container {
  font-size: 3rem;
  padding: 0rem 1rem;

  position: absolute;
  top: 0;
  right: 0;

  user-select: none;

  display: flex;
  justify-content: center;
  align-items: center;
}

.container:focus-within,
.container:focus {
  border: 1px solid black;
}

.toggle {
  display: none;
}
</style>
