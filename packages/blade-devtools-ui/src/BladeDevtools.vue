<script setup lang="ts">
import Devtools from '@/components/Devtools.vue'
import DevtoolsToggle from '@/components/DevtoolsToggle.vue'

import { ref } from 'vue'

const open = ref(false)
</script>

<template>
  <Devtools :open="open" />
  <DevtoolsToggle v-model="open" />
</template>
