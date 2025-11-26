<template>
  <component
    :is="isHeader ? 'th' : 'td'"
    :colspan="colSpan"
    :scope="isHeader ? 'col' : undefined"
    :class="[
      isHeader
        ? 'px-6 py-4 font-semibold text-gray-700 text-start text-xs uppercase tracking-wider dark:text-gray-300'
        : 'px-6 py-4 text-gray-600 text-start text-sm dark:text-gray-300 transition-colors font-mono',
      customClass
    ]"
  >
    <slot />
  </component>
</template>
<script setup>
defineProps({
  isHeader: {
    type: Boolean,
    default: false
  },
  colSpan: {
    type: [String, Number],
    default: undefined
  },
  customClass: {
    type: String,
    default: ''
  }
});
</script>