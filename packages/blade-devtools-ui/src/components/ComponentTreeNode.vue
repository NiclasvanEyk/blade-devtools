<script lang="ts" setup>
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { computed, ref, watch } from 'vue'

const emit = defineEmits<{
  (e: 'select', component: BladeComponentViewTreeNode): void
  (e: 'select-next-sibling'): void
  (e: 'select-previous-sibling'): void
}>()

const props = defineProps<{
  component: BladeComponentViewTreeNode
  selectedComponent: BladeComponentViewTreeNode | null
}>()
const hasChildren = computed(() => props.component.children.length > 0)

const domNode = ref<HTMLLIElement | null>(null)
const isSelected = computed(() => props.component.id === props.selectedComponent?.id)
watch(isSelected, (wasSelected) => {
  if (!domNode.value) return

  if (wasSelected) {
    console.log(`Selected ${props.component.label}`)
    domNode.value.focus()
  } else domNode.value.blur()
})

// this auto-expands the root node and its direct children
const expanded = ref(!props.component.parent?.parent)

function selectNextSibling(fromChildren = false) {
  const parent = props.component.parent
  if (!parent) return

  if (!fromChildren && expanded.value && props.component.children.length > 0) {
    console.log(fromChildren)
    console.log(`${props.component.label} decided to select its first children!`)
    emit('select', props.component.children[0])
    return
  }

  const siblings = parent.children
  const nextIndex = siblings.indexOf(props.component) + 1

  if (nextIndex < siblings.length) {
    console.log(`${props.component.label} decided to select ${siblings[nextIndex].label}!`)
    emit('select', siblings[nextIndex])
  } else {
    console.log(`${props.component.label} delegated the selection upwards`)
    emit('select-next-sibling')
  }
}

function selectPreviousSibling() {
  const parent = props.component.parent
  if (!parent) return

  const siblings = parent.children
  const previousIndex = siblings.indexOf(props.component) - 1

  if (previousIndex >= 0) {
    console.log(`${props.component.label} decided to select ${siblings[previousIndex].label}!`)
    emit('select', siblings[previousIndex])
  } else {
    console.log(`${props.component.label} delegated the selection upwards`)
    emit('select-previous-sibling')
  }
}
</script>

<template>
  <li
    ref="domNode"
    class="node"
    :class="{ selected: isSelected }"
    @keydown.enter.prevent.self="expanded = !expanded"
    @keydown.space.prevent.self="expanded = !expanded"
    @keydown.left.prevent.self="expanded = false"
    @keydown.right.prevent.self="expanded = true"
    @keydown.up.prevent.self="selectPreviousSibling()"
    @keydown.down.prevent.self="selectNextSibling()"
    tabindex="1"
  >
    <div
      v-if="props.component.parent !== null"
      class="tag"
      @click="emit('select', props.component)"
    >
      <button
        v-bind:class="{ invisible: !hasChildren }"
        @click="expanded = !expanded"
        class="expanded-indicator"
      >
        <span v-if="!expanded">▶</span>
        <span v-if="expanded">▼</span>
      </button>

      <span v-if="hasChildren">&lt;{{ component.label }}&gt;</span>
      <span v-else>&lt;{{ component.label }} /&gt;</span>
    </div>

    <ul v-if="hasChildren && expanded" class="children">
      <ComponentTreeNode
        @select="emit('select', $event)"
        @select-previous-sibling="selectPreviousSibling(true)"
        @select-next-sibling="selectNextSibling(true)"
        v-for="child in component.children"
        v-bind:key="child.id"
        :component="child"
        :selected-component="selectedComponent"
      />
    </ul>
  </li>
</template>

<style>
.node {
  font-family: monospace;
  list-style-type: none;
}

.tag:hover {
  background-color: var(--red-300);
}

.selected > .tag {
  background-color: var(--red-500);
}

.expanded-indicator {
  margin-right: 0.5rem;
}

.children {
  margin-left: 10px;
}

.invisible {
  visibility: hidden;
}
</style>