<script setup lang="ts">
import { ref } from 'vue'
import ComponentTreeNode from '@/components/ComponentTreeNode.vue'
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { getAllComments } from '@/lib/blade'

defineProps<{ open: boolean }>()

const [root] = getAllComments()

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
  <div class="blade-devtools" v-bind:class="{ open }">
    <h3 class="headline">Blade Devtools</h3>

    <div class="main-detail">
      <div class="main">
        <ComponentTreeNode
          :component="root"
          :level="0"
          :selected-component="selectedComponent"
          @select="selectedComponent = $event"
          @select-previous-sibling="selectPreviousSibling()"
          @select-next-sibling="selectNextSibling()"
        />
      </div>
      <div class="detail">
        <pre>{{ JSON.stringify(selectedComponent?.data, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<style scoped>
.blade-devtools.open {
  transform: translateY(0);
}

.blade-devtools {
  position: fixed;
  bottom: 0;
  inset-inline: 0;
  height: 33vh;
  transform: translateY(100%);
  transition: transform 0.25s ease-in-out;
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
  white-space: pre;
}

.main {
  border-right: 1px solid black;
}

.detail {
  padding: 0 1rem 1rem;
}
</style>
