<script setup lang="ts">
defineProps<{ value: string }>()
</script>

<template>
    <span class="value">"{{ value }}"</span>
</template>

<style scoped>
.value {
    color: var(--text-color-string);
}
</style>