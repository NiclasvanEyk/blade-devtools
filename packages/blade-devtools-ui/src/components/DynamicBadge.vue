<script lang="ts" setup>
const props = defineProps<{ class?: string }>()
</script>

<template>
  <span v-bind="$props" v-bind:class="{ badge: true, [props.class ?? '']: true }"> dynamic </span>
</template>

<style scoped>
.badge {
  border-radius: 100px;
  padding: 1px 5px;
  font-size: 0.75em;
  background-color: var(--red-200);
  color: var(--red-900);
}
</style>
