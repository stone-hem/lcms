<template>
  <div class="relative w-full" ref="dropdownRef">
    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ label }}</label>
    <details class="relative p-2 cursor-pointer w-full rounded-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-3 px-4.5 font-normal text-gray-900 dark:text-gray-100 focus:border-blue-500 focus-visible:outline-hidden shadow-xs transition-colors">
      <summary class="font-medium text-gray-700 dark:text-gray-200 flex justify-between items-center">
        <span v-if="internalValue.length > 0">
          {{ internalValue.map(item => item[labelKey]).join(', ') }}
        </span>
        <span v-else class="text-gray-500 dark:text-gray-400">{{ placeholder }}</span>
        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </summary>

      <div class="absolute top-full left-0 z-10 shadow-lg mt-2 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 px-3 font-normal text-gray-900 dark:text-gray-100 focus:border-blue-500 focus-visible:outline-hidden">
        <input
          type="search"
          v-model="searchTerm"
          placeholder="Search items..."
          class="w-full px-3 py-2 mb-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-hidden focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
        />
        <div class="max-h-60 overflow-y-auto">
          <div
            v-for="(item, index) in filteredOptions"
            :key="item[valueKey]"
            class="flex items-center space-x-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
          >
            <input
              type="checkbox"
              :id="`item-${item[valueKey]}`"
              :value="item"
              :checked="internalValue.some(i => i[valueKey] === item[valueKey])"
              @change="toggleItem(item)"
              class="cursor-pointer w-4 h-4 text-blue-600 rounded-sm border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700"
            />
            <label :for="`item-${item[valueKey]}`" class="cursor-pointer text-gray-700 dark:text-gray-200 select-none">
              {{ item[labelKey] }}
            </label>
          </div>
        </div>
        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            @click="handleDone"
            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500"
          >
            Done
          </button>
        </div>
      </div>
    </details>

    <!-- Display selected items as chips -->
    <div class="mt-4 flex flex-wrap gap-2">
      <div
        v-for="(item, index) in internalValue"
        :key="item[valueKey]"
        class="flex items-center gap-1.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 px-3 py-1 rounded-full transition-colors"
      >
        <span>{{ item[labelKey] }}</span>
        <button
          type="button"
          @click="removeItem(item)"
          class="text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition-colors"
        >
          &times;
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { onClickOutside } from "@vueuse/core";

const props = defineProps({
  options: { type: Array, required: true },
  modelValue: { type: Array, default: () => [] },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'name' },
  placeholder: { type: String, default: 'Select Items' },
  label: { type: String, default: 'Select Items' },
  returnIdsOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'change']);

const internalValue = ref([...props.modelValue]);
const dropdownRef = ref(null);
const searchTerm = ref('');

const filteredOptions = computed(() => {
  if (!searchTerm.value) return props.options;
  return props.options.filter(item => 
    item[props.labelKey].toLowerCase().includes(searchTerm.value.toLowerCase())
  );
});

const uniqueValues = computed(() => {
  const map = new Map();
  internalValue.value.forEach(item => map.set(item[props.valueKey], item));
  return Array.from(map.values());
});

watch(uniqueValues, (newVal) => {
  if (props.returnIdsOnly) {
    emit('update:modelValue', [...new Set(newVal.map(item => item[props.valueKey]))]);
  } else {
    emit('update:modelValue', newVal);
  }
});

const toggleItem = (item) => {
  const exists = internalValue.value.some(i => i[props.valueKey] === item[props.valueKey]);
  if (exists) {
    internalValue.value = internalValue.value.filter(i => i[props.valueKey] !== item[props.valueKey]);
  } else {
    internalValue.value.push(item);
  }
};

const removeItem = (item) => {
  internalValue.value = internalValue.value.filter(i => i[props.valueKey] !== item[props.valueKey]);
};

const handleDone = () => {
  const details = dropdownRef.value.querySelector('details');
  if (details) details.open = false;

  const emitValue = props.returnIdsOnly 
    ? [...new Set(uniqueValues.value.map(item => item[props.valueKey]))]
    : uniqueValues.value;

  emit('change', emitValue);
};

onClickOutside(dropdownRef, () => {
  const details = dropdownRef.value.querySelector('details');
  if (details) details.open = false;
});
</script>
