<script setup lang="ts">
   import type {ArrayComponentAttribute} from "@/lib/blade";
   import Value from "@/components/data-types/Value.vue";
   import String from "@/components/data-types/String.vue";
   import ExpandedIndicator from "@/components/ExpandedIndicator.vue";
   import {computed, ref} from "vue";
   import Number from "@/components/data-types/Number.vue";

   const props = defineProps<{
       attribute: ArrayComponentAttribute
       initial?: boolean
   }>()

   const isEmpty = computed(() => Object.keys(props.attribute.value).length <= 0)
   const expanded = ref(true)
</script>

<template>
  <span v-if="!initial">[</span>
  <button v-if="!initial && !isEmpty" @click="expanded = !expanded">
      <ExpandedIndicator :expanded="expanded" />
  </button>
  <div v-if="expanded" v-for="(child, name) in attribute.value" class="attribute" v-bind:class="{ padded : !initial }">
      <span v-if="!attribute.list">
          <String v-if="typeof name === 'string'" :value="name" />
          <Number v-else :value="name" /> =>
      </span>
      <Value :attribute="child" />,
  </div>
  <span v-if="!initial">]</span>
</template>

<style scoped>
.attribute {
    display: block;
    white-space: pre;
    font-family: monospace;
}
.padded {
    padding-left: 2rem;
}
</style>