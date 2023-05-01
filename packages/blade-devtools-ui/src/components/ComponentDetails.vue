<script setup lang="ts">
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { computed } from 'vue'
import Array from '@/components/data-types/Array.vue'

const props = defineProps<{
  selectedComponent: BladeComponentViewTreeNode | null
}>()

const data = computed(() => {
  if (!props.selectedComponent) return {}

  const ret = window.__BDT_CONTEXT[props.selectedComponent.id];
    console.log(ret);

    return ret;
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