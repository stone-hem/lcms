<template>
  <div class="mt-4">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}<span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="textareaClasses"
      v-bind="$attrs"
    ></textarea>
  </div>
</template>

<script setup>
import { computed, useAttrs } from 'vue'

defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])

const attrs = useAttrs()
const required = computed(() => attrs.required !== undefined)

const textareaClasses = 'mt-1 block w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 resize-vertical min-h-[100px]'
</script>