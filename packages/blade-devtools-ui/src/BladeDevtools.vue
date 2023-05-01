<script setup lang="ts">
import Devtools from '@/components/Devtools.vue'
import DevtoolsToggle from '@/components/DevtoolsToggle.vue'

import { ref } from 'vue'

const open = ref(false)
</script>

<template>
  <div class="blade-devtools-container">
      <div class="blade-devtools" id="blade-devtools">
          <Devtools :open="open" />
          <DevtoolsToggle v-model="open" />
      </div>
  </div>
</template>

<style scoped>
.blade-devtools-container {
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

  /* This enables us to position everything relative to the top left of the screen */
  position: absolute;
  top: 0;
  left: 0;
  height: 0;
  width: 0;
}

.blade-devtools {
    position: relative;
}
</style>