<script setup lang="ts">
import type {ClassComponentAttribute} from "@/lib/blade";
import Value from "@/components/data-types/Value.vue";
import {ref} from "vue";
import ExpandedIndicator from "@/components/ExpandedIndicator.vue";

defineProps<{
    className: string
    properties: ClassComponentAttribute['properties']
}>()

const expanded = ref(true)
</script>

<template>
    <button @click="expanded = !expanded">
        <span class="class-name">{{ className }}&nbsp;</span>
        <ExpandedIndicator :expanded="expanded" />
    </button>
    <div v-if="expanded" class="children">
        <div v-for="property in properties">
            <span>{{ property.name }}:&nbsp;</span>
            <Value :attribute="property.value" />
        </div>
    </div>
</template>

<style scoped>
.class-name {
    color: darkviolet;
    font-style: italic;
}

.children {
    padding-left: 2rem;
}
</style>