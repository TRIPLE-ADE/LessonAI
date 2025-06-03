<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        :class="className"
        width="35"
        height="35"
        viewBox="0 0 40 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <!-- Circle background -->
        <circle cx="20" cy="20" r="16" stroke="#000000" stroke-width="2" fill="none"/>
        <!-- ST Monogram -->
        <path
            d="M14 12H26M14 20H26M14 28H26"
            stroke="#000000"
            stroke-width="2"
            stroke-linecap="round"
        />
        <path
            d="M14 12L26 28M26 12L14 28"
            stroke="#000000"
            stroke-width="2"
            stroke-linecap="round"
        />
    </svg>
</template>
