<script setup lang="ts">
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { computed, watch } from 'vue'

const props = defineProps<{
  selectedComponent: BladeComponentViewTreeNode | null
}>()

const categorizedProps = computed(() => {
  const { selectedComponent } = props
  const categorizedProps = { dom: {}, other: {} }

  if (!selectedComponent) return categorizedProps

  const { element, data } = selectedComponent
  if (!(element instanceof HTMLElement)) return categorizedProps

  Object.entries(data).forEach(([name, value]) => {
    const attribute = element.attributes.getNamedItem(name)
    if (name == 'class' || (attribute && attribute.value == value)) {
      categorizedProps.dom[name] = value
    } else if (element[name] && element[name] == value) {
      categorizedProps.dom[name] = value
    } else {
      categorizedProps.other[name] = value
    }
  })

  return categorizedProps
})

watch(categorizedProps, console.log)

const render = (properties) =>
  JSON.stringify(properties, null, 2)
    .split('\n')
    .map((line) => line.substring(2))
    .slice(1, -1)
    .join('\n')
</script>

<template>
  <!-- <h3>DOM Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.dom) }}</pre> -->
  <!---->
  <!-- <h3>Other Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.other) }}</pre> -->

  <pre>{{ selectedComponent?.data_serialized }}</pre>

  <div v-html="selectedComponent?.data_dumped"></div>
</template>
