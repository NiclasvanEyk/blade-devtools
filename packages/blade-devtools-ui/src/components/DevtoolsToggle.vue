<script setup lang="ts">
const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ (e: 'update:modelValue', payload: boolean) }>()

const toggle = () => emit('update:modelValue', !props.modelValue)
</script>

<template>
  <label
    class="container"
    v-bind:class="{ open: modelValue }"
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

  border: 1px solid transparent;

  user-select: none;

  display: flex;
  justify-content: center;
  align-items: center;
}

.container:focus-within,
.container:focus {
  border-color: black;
}

.open {
  color: var(--red-800);
  text-shadow: 0px 0px 2px var(--red-300), 0px 0px 4px var(--red-600);
}

.toggle {
  display: none;
}
</style>
