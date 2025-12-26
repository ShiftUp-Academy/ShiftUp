<script setup>
const props = defineProps({
  title: { type: String, default: '' }
});
</script>

<template>
  <div class="rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] shadow-sm">
    <div v-if="props.title" class="px-4 py-3 border-b border-[#e3e3e0] dark:border-[#3E3E3A] font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
      {{ props.title }}
    </div>
    <div class="p-4">
      <slot />
    </div>
  </div>
</template>