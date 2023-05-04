<script setup lang="ts">
import type {BladeComponentViewTreeNode} from '@/lib/tree-view'
import {computed} from 'vue'
import Array from '@/components/data-types/Array.vue'
import {Tab, TabGroup, TabList, TabPanel, TabPanels} from "@headlessui/vue";

const props = defineProps<{
  selectedComponent: BladeComponentViewTreeNode | null
}>()

const data = computed(() => {
  if (!props.selectedComponent) return {}

  return window.__BLADE_DEVTOOLS_COMPONENT_DATA[props.selectedComponent.id];
})
</script>

<template>
    <TabGroup>
        <TabList class="tab-list">
            <Tab as="template" v-slot="{ selected }">
                <button class="tab" v-bind:class="{ selected: selected }">
                    Attributes
                </button>
            </Tab>
            <Tab as="template" v-slot="{ selected }">
                <button class="tab" v-bind:class="{ selected: selected }">
                    View
                </button>
            </Tab>
        </TabList>
        <TabPanels class="tab-panels">
            <TabPanel>
                <Array v-if="data.data" :initial="true" :attribute="data.data" />
            </TabPanel>
            <TabPanel>
                <dl>
                    <dt>Tag:</dt> <dd>&lt;x-{{ data.tag }}&gt;</dd><br/>
                    <dt>View:</dt> <dd>{{ data.view }}</dd><br/>
                    <dt>File:</dt> <dd>
                    <a :href="data.file_open_url">{{ data.file }}</a>
                </dd><br/>
                </dl>

                <code>
                    <pre>{{ JSON.stringify(data, null, 2) }}</pre>
                </code>
            </TabPanel>
        </TabPanels>
    </TabGroup>

  <!-- Button for opening the view in your editor  -->
  <!-- Button for opening the compiled view in your editor  -->
  <!-- Button for opening the class in your editor  -->
  <!-- Button for revealing the component in the elements tab  -->

  <!-- <h3>DOM Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.dom) }}</pre> -->
  <!---->
  <!-- <h3>Other Properties</h3> -->
  <!-- <pre>{{ render(categorizedProps.other) }}</pre> -->
</template>

<style scoped>
.tab {
    opacity: 0.8;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    font-size: 1rem;
}

dt, dd {
    display: inline-block;
}

.selected {
    opacity: 1;
    background: var(--primary-700);
}

.tab ~ .tab {
    margin-left: 1rem;
}

.tab-panels {
    margin-top: 0.5rem;
}
</style>