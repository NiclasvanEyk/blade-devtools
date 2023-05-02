<script setup lang="ts">
defineProps<{ value: boolean }>()
</script>

<template>
    <span class="value">{{ value }}</span>
</template>

<style scoped>
.value {
    color: var(--text-color-boolean);
}
</style>