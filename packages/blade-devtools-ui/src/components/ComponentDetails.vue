<script setup lang="ts">
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import {computed, toRaw, watch} from 'vue'

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
  <!-- <h3>DOM Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.dom) }}</pre> -->
  <!---->
  <!-- <h3>Other Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.other) }}</pre> -->

  <pre>{{ render(data) }}</pre>
</template>