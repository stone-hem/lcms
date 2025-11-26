<template>
  <div class="mt-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}<span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :class="selectClasses"
      v-bind="$attrs"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <slot />
    </select>
  </div>
</template>

<script setup>
import { computed, useAttrs } from 'vue'

defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const attrs = useAttrs()
const required = computed(() => attrs.required !== undefined)

const selectClasses = 'mt-1 block w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'
</script>