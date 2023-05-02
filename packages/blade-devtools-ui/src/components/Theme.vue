<script setup lang="ts">
</script>

<template>
<div class="blade-devtools-container">
    <slot />
</div>
</template>

<style scoped>
.blade-devtools-container {
    /* This enables us to position everything relative to the top left of the screen */
    position: absolute;
    top: 0;
    left: 0;
    height: 0;
    width: 0;

    --primary-50: #fef2f2;
    --primary-100: #fee2e2;
    --primary-200: #fecaca;
    --primary-300: #fca5a5;
    --primary-400: #f87171;
    --primary-500: #ef4444;
    --primary-600: #dc2626;
    --primary-700: #b91c1c;
    --primary-800: #991b1b;
    --primary-900: #7f1d1d;
    --primary-950: #450a0a;

    --border-color: rgba(0, 0, 0, 0.15);
    --background-color: white;

    --text-color: black;
    --text-color-string: darkgreen;
    --text-color-number: blue;
    --text-color-boolean: red;
    --text-color-class: darkviolet;
    --text-color-null: royalblue;
}

@media (prefers-color-scheme: dark) {
    .blade-devtools-container {
        --border-color: rgba(255, 255, 255, 0.15);
        --background-color: rgb(23, 25, 35);
        --text-color: white;

        /* Inspired by the syntax highlighting on bootcamp.laravel.com */
        --text-color-string: #C3E88D;
        --text-color-number: #82AAFF;
        --text-color-boolean: red;
        --text-color-class: #C792EA;
        --text-color-null: #82AAFF;
    }
}
</style>