<script setup lang="ts">
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import {computed} from 'vue'
import Value from "@/components/data-types/Value.vue";
import Array from "@/components/data-types/Array.vue";

const props = defineProps<{
  selectedComponent: BladeComponentViewTreeNode | null
}>()

const render = function (properties) {
    console.log(properties);

    return JSON.stringify(properties, null, 2)
        .split('\n')
        .map((line) => line.substring(2))
        .slice(1, -1)
        .join('\n')
}

const data = computed(() => {
    if (!props.selectedComponent) return {};

    return window.__BDT_CONTEXT[props.selectedComponent.id];
})
</script>

<template>
  <!-- Button for opening the view in your editor  -->
  <!-- Button for opening the compiled view in your editor  -->
  <!-- Button for opening the class in your editor  -->
  <!-- Button for revealing the component in the elements tab  -->

  <!-- <h3>DOM Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.dom) }}</pre> -->
  <!---->
  <!-- <h3>Other Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.other) }}</pre> -->
  <h3>Attributes</h3>
  <Array v-if="data.data" :initial="true" :attribute="data.data" />
</template>