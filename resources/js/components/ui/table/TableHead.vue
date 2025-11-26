<template>
  <th
    :colspan="colSpan"
    scope="col"
    class="px-6 py-4 font-semibold text-gray-700 text-start text-xs uppercase tracking-wider dark:text-gray-300"
    :class="customClass"
  >
    <slot />
  </th>
</template>

<script setup>
defineProps({
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