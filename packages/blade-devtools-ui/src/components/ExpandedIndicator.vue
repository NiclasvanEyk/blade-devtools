<template>
    <span v-if="!expanded" v-bind="attrs">▶</span>
    <span v-if="expanded" v-bind="attrs">▼</span>
</template>

<script setup lang="ts">
import {useAttrs} from "vue";

defineProps<{expanded: boolean}>()

const attrs = useAttrs()
</script>