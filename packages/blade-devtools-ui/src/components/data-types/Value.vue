<script setup lang="ts">
import Array from "@/components/data-types/Array.vue";
import Class from "@/components/data-types/Class.vue";
import String from "@/components/data-types/String.vue";
import Number from "@/components/data-types/Number.vue";
import type {ComponentAttribute} from "@/lib/blade";
import Boolean from "@/components/data-types/Boolean.vue";

defineProps<{ attribute: ComponentAttribute }>();
</script>

<template>
    <String v-if="attribute.type === 'string'" :value="attribute.value" />
    <Number v-else-if="attribute.type === 'int' " :value="attribute.value" />
    <Number v-else-if="attribute.type === 'float'" :value="attribute.value" />
    <Boolean v-else-if="attribute.type === 'bool'" :value="attribute.value" />
    <Array v-else-if="attribute.type === 'array'" :attribute="attribute" />
    <Class v-else-if="attribute.type === 'class'" :class-name="attribute.class" :properties="attribute.properties" />
</template>