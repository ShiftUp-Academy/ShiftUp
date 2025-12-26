<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'primary' },
  type: { type: String, default: 'button' }
});

const classes = computed(() => {
  const base = 'inline-flex items-center justify-center rounded-sm px-4 py-2 text-sm';
  const theme = props.variant === 'secondary'
    ? 'bg-white border border-[#e3e3e0] text-[#1b1b18] hover:bg-[#f5f5f4] dark:bg-[#161615] dark:text-[#EDEDEC] dark:border-[#3E3E3A]'
    : 'bg-[#1b1b18] text-white hover:bg-black';
  return `${base} ${theme}`;
});
</script>

<template>
  <button :type="props.type" :class="classes"><slot /></button>
  </template>