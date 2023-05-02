<script setup lang="ts">
defineProps<{ value: number }>()
</script>

<template>
    <span class="value">{{ value }}</span>
</template>

<style scoped>
.value {
    color: var(--text-color-number);
}
</style>