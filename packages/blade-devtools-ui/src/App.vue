<script setup lang="ts">
import { ref, toRaw } from 'vue'
import ComponentTreeNode from './components/ComponentTreeNode.vue'
import type { BladeComponentViewTreeNode } from './lib/tree-view'

const [root, globalComponentList] = (window as any).__BLADE_DEVTOOLS_ROOT__ as [
  BladeComponentViewTreeNode,
  BladeComponentViewTreeNode[]
]

root.expanded = true

const selectedComponent = ref<null | BladeComponentViewTreeNode>(null)
function selectPreviousSibling() {
  if (!selectedComponent.value) return
  const index = root.children.indexOf(selectedComponent.value)
  console.log(index - 1)
  selectedComponent.value = root.children[Math.max(0, index - 1)]
}

function selectNextSibling() {
  if (!selectedComponent.value) return
  const index = root.children.indexOf(selectedComponent.value)
  console.log(index + 1)
  selectedComponent.value = root.children[Math.min(root.children.length, index + 1)]
}
</script>

<template>
  <div class="blade-devtools">
    <h3 class="headline">Blade Devtools</h3>

    <div class="main-detail">
      <div class="main">
        <ComponentTreeNode
          :component="root"
          :selected-component="selectedComponent"
          @select="selectedComponent = $event"
          @select-previous-sibling="selectPreviousSibling()"
          @select-next-sibling="selectNextSibling()"
        />
      </div>
      <div class="detail">
        {{ selectedComponent?.label }}
        selected: {{ selectedComponent?.id }}
        <pre>{{ JSON.stringify(selectedComponent?.data, null, 4) }}</pre>
      </div>
    </div>
  </div>
</template>

<style scoped>
.headline {
  font-size: 2rem;
}

.main-detail {
  display: flex;
  flex-direction: row;
}

.main,
.detail {
  max-height: 100vh;
  max-width: 100%;
  overflow: auto;
  padding: 2rem;
  white-space: pre;
}

.main {
  border-right: 1px solid black;
}

.blade-devtools {
  position: fixed;
  top: 0;
  inset-inline: 0;
  background: white;
  color: black;
  z-index: 999999999;

  --red-50: #fef2f2;
  --red-100: #fee2e2;
  --red-200: #fecaca;
  --red-300: #fca5a5;
  --red-400: #f87171;
  --red-500: #ef4444;
  --red-600: #dc2626;
  --red-700: #b91c1c;
  --red-800: #991b1b;
  --red-900: #7f1d1d;
  --red-950: #450a0a;
}
</style>
