<script setup lang="ts">
import { ref } from 'vue'
import ComponentTree from '@/components/ComponentTree.vue'
import ComponentDetails from '@/components/ComponentDetails.vue'
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { getAllComments } from '@/lib/blade'

defineProps<{ open: boolean }>()

const [root] = getAllComments()

const selectedComponent = ref<null | BladeComponentViewTreeNode>(null)
</script>

<template>
  <div class="blade-devtools" v-bind:class="{ open }">
    <div class="main-detail">
      <div class="main">
        <ComponentTree
          @selected="selectedComponent = $event"
          :root="root"
          :selected-component="selectedComponent"
        />
      </div>
      <div class="detail">
        <ComponentDetails :selected-component="selectedComponent" />
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
  transition: transform 0.15s ease-in-out;
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

  --border-color: rgba(0, 0, 0, 0.15);
}

.main-detail {
  display: flex;
  flex-direction: row;
  height: 100%;
}

.main,
.detail {
  padding: 0.5rem;
  max-height: 100vh;
  max-width: 100%;
  width: 50%;
  height: 100%;
  overflow: auto;
  white-space: pre;
}

.main {
  border-right: 1px solid var(--border-color);
}

.detail {
  padding: 0 1rem 1rem;
}
</style>
