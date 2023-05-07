<script setup lang="ts">
import Devtools from '@/components/Devtools.vue'
import DevtoolsToggle from '@/components/DevtoolsToggle.vue'

import { ref } from 'vue'
import Theme from "@/components/Theme.vue";
import {
    type Overlay,
    provideComponentHighlighting
} from "@/lib/highlight-dom-element";
import ComponentHighlight from "@/components/ComponentHighlight.vue";

const open = ref(false)
const overlay = ref<Overlay | null>(null)
provideComponentHighlighting(overlay);
</script>

<template>
  <Theme>
      <div class="blade-devtools" id="blade-devtools">
          <Devtools :open="open" />
          <DevtoolsToggle v-model="open" />
          <ComponentHighlight :overlay="overlay" />
      </div>
  </Theme>
</template>

<style scoped>
.blade-devtools {
    position: relative;
}
</style>
