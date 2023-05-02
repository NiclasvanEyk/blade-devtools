<script setup lang="ts">
import {ref} from 'vue'
import ComponentTree from '@/components/ComponentTree.vue'
import ComponentDetails from '@/components/ComponentDetails.vue'
import type { BladeComponentViewTreeNode } from '@/lib/tree-view'
import { parseComponentTree } from '@/lib/blade'

defineProps<{ open: boolean }>()

const componentTree = parseComponentTree()

const selectedComponent = ref<null | BladeComponentViewTreeNode>(null)
</script>

<template>
  <div class="devtools" v-bind:class="{ open }">
    <div class="main-detail">
      <div class="main">
        <ComponentTree
          @selected="selectedComponent = $event"
          :root="componentTree"
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
.devtools.open {
  transform: translateY(0);
}

.devtools {
  border-top: 1px solid var(--border-color);
  position: fixed;
  bottom: 0;
  inset-inline: 0;
  height: 33vh;
  transform: translateY(100%);
  transition: transform 0.15s ease-in-out;
  background: var(--background-color);
  color: var(--text-color);
  z-index: 999999999;
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
</style>