<template>
  <div class="relative w-full" ref="dropdownRef">
    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ label }}</label>
    <details class="relative p-2 cursor-pointer w-full rounded-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-3 px-4.5 font-normal text-gray-900 dark:text-gray-100 focus:border-blue-500 focus-visible:outline-hidden shadow-xs transition-colors">
      <summary class="font-medium text-gray-700 dark:text-gray-200 flex justify-between items-center">
        <span v-if="internalValue">
          {{ internalValue[labelKey] }}
        </span>
        <span v-else class="text-gray-500 dark:text-gray-400">{{ placeholder }}</span>
        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </summary>

      <div class="absolute top-full left-0 z-10 shadow-lg mt-2 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-3 font-normal text-gray-900 dark:text-gray-100 focus:border-blue-500 focus-visible:outline-hidden">
        <Input
          type="search"
          v-model="searchTerm"
          placeholder="Search items..."
        />
        <div class="max-h-60 overflow-y-auto">
          <div
            v-for="(item, index) in filteredOptions"
            :key="item[valueKey]"
            class="flex items-center space-x-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors cursor-pointer"
            @click="selectItem(item)"
          >
            <span class="text-gray-700 dark:text-gray-200 select-none">
              {{ item[labelKey] }}
            </span>
          </div>
        </div>
      </div>
    </details>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { onClickOutside } from "@vueuse/core";
import Input from '@/components/forms/Input.vue';


const props = defineProps({
  options: { type: Array, required: true },
  modelValue: { type: Object || String, default: null },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'name' },
  placeholder: { type: String, default: 'Select Item' },
  label: { type: String, default: 'Select Item' },
  returnIdsOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'change']);

const internalValue = ref(props.modelValue);
const dropdownRef = ref(null);
const searchTerm = ref('');

const filteredOptions = computed(() => {
  if (!searchTerm.value) return props.options;
  return props.options.filter(item => 
    item[props.labelKey].toLowerCase().includes(searchTerm.value.toLowerCase())
  );
});

watch(internalValue, (newVal) => {
  if (props.returnIdsOnly) {
    emit('update:modelValue', newVal ? newVal[props.valueKey] : null);
  } else {
    emit('update:modelValue', newVal);
  }
  emit('change', newVal);
});

const selectItem = (item) => {
  internalValue.value = item;
  const details = dropdownRef.value.querySelector('details');
  if (details) details.open = false;
  searchTerm.value = '';
};

onClickOutside(dropdownRef, () => {
  const details = dropdownRef.value.querySelector('details');
  if (details) details.open = false;
});
</script>
