<script setup lang="ts">
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import ComponentTreeNode from '@/components/ComponentTreeNode.vue'

const props = defineProps<{
  root: BladeComponentViewTreeNode
  selectedComponent: BladeComponentViewTreeNode | null
}>()
const emit = defineEmits<{ (e: 'selected', component: ComponentTreeNode) }>()

function selectPreviousSibling() {
  if (!props.selectedComponent) return

  const index = props.root.children.indexOf(props.selectedComponent)
  emit('selected', props.root.children[Math.max(0, index - 1)])
}

function selectNextSibling() {
  if (!props.selectedComponent) return

  const index = props.root.children.indexOf(props.selectedComponent)
  emit('selected', props.root.children[Math.min(props.root.children.length, index + 1)])
}
</script>

<template>
  <div>
    <ComponentTreeNode
      :component="root"
      :level="0"
      :selected-component="selectedComponent"
      @select="$emit('selected', $event)"
      @select-previous-sibling="selectPreviousSibling()"
      @select-next-sibling="selectNextSibling()"
    />
  </div>
</template>
