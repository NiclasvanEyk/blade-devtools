<script lang="ts" setup>
import type {Overlay} from '@/lib/highlight-dom-element'
import {computed} from "vue";

const props = defineProps<{ overlay: Overlay|null }>()

function bounds(overlay: Overlay) {
    return {
        top: `${overlay.position.y}px`,
        right: `${overlay.position.x + overlay.dimensions.width}px`,
        bottom: `${overlay.position.y + overlay.dimensions.height}px`,
        left: `${overlay.position.x}px`,
    }
}

const overlayStyle = computed(() => {
    if (!props.overlay) return {}

    const style: Partial<HTMLElement['style']> = {
        left: `${props.overlay.position.x}px`,
        top: `${props.overlay.position.y}px`,
        width: `${props.overlay.dimensions.width}px`,
        height: `${props.overlay.dimensions.height}px`,
    }

    return style
})
</script>

<template>
    <div v-if="overlay" class="highlight">
        <div class="line top" v-bind:style="{ top: bounds(overlay).top, left: 0 }"></div>
        <div class="line right" v-bind:style="{ left: bounds(overlay).right, top: 0 }"></div>
        <div class="line bottom" v-bind:style="{ top: bounds(overlay).bottom, left: 0 }"></div>
        <div class="line left" v-bind:style="{ left: bounds(overlay).left, top: 0 }"></div>

        <div class="element" v-bind:style="overlayStyle"></div>
    </div>
</template>


<style scoped>
.highlight {
    --very-large-z-index: 999999998;
    --border-width: 1px;
    --border: var(--border-width) dashed var(--primary-500);
}

.line {
    position: fixed;
    z-index: var(--very-large-z-index);
}

.top {
    border-top: var(--border);
    height: var(--border-width);
    width: 100vw;
}
.right {
    border-right: var(--border);
    width: var(--border-width);
    height: 100vh;
}
.bottom {
    border-bottom: var(--border);
    height: var(--border-width);
    width: 100vw;
}
.left {
    border-left: var(--border);
    width: var(--border-width);
    height: 100vh;
}

.element {
    position: fixed;
    z-index: var(--very-large-z-index);
    background: var(--primary-500);
    opacity: 0.3;

    font-family: monospace;
    pointer-events: none;
}
</style>