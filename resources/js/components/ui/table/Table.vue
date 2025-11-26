<template>
  <div class="overflow-x-auto rounded-xl border border-black-200 dark:border-white/[0.08] shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.08]">
      <slot />
    </table>
  </div>
</template>
<script setup>
defineProps({});
</script>