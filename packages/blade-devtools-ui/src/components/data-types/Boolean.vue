<script setup lang="ts">
defineProps<{ value: boolean }>()
</script>

<template>
    <span class="value">{{ value }}</span>
</template>

<style scoped>
.value {
    color: red;
}
</style>