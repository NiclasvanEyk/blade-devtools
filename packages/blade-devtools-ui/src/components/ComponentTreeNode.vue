<script lang="ts" setup>
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { computed, ref, watch } from 'vue'
import DynamicBadge from '@/components/DynamicBadge.vue'
import ExpandedIndicator from "@/components/ExpandedIndicator.vue";

const emit = defineEmits<{
  (e: 'select', component: BladeComponentViewTreeNode): void
  (e: 'select-next-sibling'): void
  (e: 'select-previous-sibling'): void
}>()

const props = defineProps<{
  component: BladeComponentViewTreeNode
  selectedComponent: BladeComponentViewTreeNode | null
  level: number
}>()
const hasChildren = computed(() => props.component.children.length > 0)

const domNode = ref<HTMLLIElement | null>(null)
const isSelected = computed(() => props.component.id === props.selectedComponent?.id)
watch(isSelected, (wasSelected) => {
  if (!domNode.value) return

  if (wasSelected) {
    domNode.value.focus()
  } else domNode.value.blur()
})

// this auto-expands the root node and its direct children
const expanded = ref(!props.component.parent?.parent)

function selectNextSibling(fromChildren = false) {
  const parent = props.component.parent
  if (!parent) return

  if (!fromChildren && expanded.value && props.component.children.length > 0) {
    emit('select', props.component.children[0])
    return
  }

  const siblings = parent.children
  const nextIndex = siblings.indexOf(props.component) + 1

  if (nextIndex < siblings.length) {
    emit('select', siblings[nextIndex])
  } else {
    emit('select-next-sibling')
  }
}

function selectPreviousSibling() {
  const parent = props.component.parent
  if (!parent) return

  const siblings = parent.children
  const previousIndex = siblings.indexOf(props.component) - 1

  if (previousIndex >= 0) {
    emit('select', siblings[previousIndex])
  } else {
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
      :style="{ paddingLeft: `${level * 10}px` }"
      @click="emit('select', props.component)"
    >
      <button
        v-bind:class="{ invisible: !hasChildren }"
        @click="expanded = !expanded"
        class="expanded-indicator"
        tabindex="-1"
      >
          <ExpandedIndicator :expanded="expanded" />
      </button>

      <span v-if="hasChildren">&lt;{{ component.label }}&gt;</span>
      <span v-else>&lt;{{ component.label }} /&gt;</span>
      <DynamicBadge style="margin-left: 4px; display: block" v-if="component.dynamic" />
    </div>

    <ul v-if="hasChildren && expanded" class="children">
      <ComponentTreeNode
        @select="emit('select', $event)"
        @select-previous-sibling="emit('select', props.component)"
        @select-next-sibling="selectNextSibling(true)"
        v-for="child in component.children"
        v-bind:key="child.id"
        :component="child"
        :selected-component="selectedComponent"
        :level="props.level + 1"
      />
    </ul>
  </li>
</template>

<style scoped>
.node {
  font-family: monospace;
  list-style-type: none;
}

.tag {
  display: flex;
  flex-direction: row;
  align-items: center;
  padding-right: 1rem;
  user-select: none;
  border-radius: 4px;
}

.tag:hover {
  background-color: var(--red-300);
}

.selected > .tag {
  background-color: var(--red-700);
  color: var(--red-50);
}

.expanded-indicator {
  margin-right: 0.5rem;
}

.children {
  margin-left: 0;
}

.invisible {
  visibility: hidden;
}
</style>