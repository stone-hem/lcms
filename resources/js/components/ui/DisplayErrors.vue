<template>
  <div v-if="hasErrors" class="mx-8 mt-6">
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 space-y-2">
      <div class="flex items-center gap-2 font-semibold">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span>There were errors with your submission</span>
      </div>
      <ul class="ml-7 list-disc space-y-1 text-sm">
        <li v-for="(error, field) in flattenedErrors" :key="field">
          {{ error }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DisplayErrors',
  props: {
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    hasErrors() {
      return this.errors && Object.keys(this.errors).length > 0;
    },
    
    flattenedErrors() {
      const flattened = {};
      
      const flatten = (obj, prefix = '') => {
        for (const key in obj) {
          const value = obj[key];
          
          if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
            // Check if it's an error object with numeric keys (like "0": "error message")
            const keys = Object.keys(value);
            if (keys.every(k => !isNaN(k))) {
              // It's an array-like error object - extract the first error message
              const errorMessage = Object.values(value)[0];
              flattened[prefix ? `${prefix}.${key}` : key] = errorMessage;
            } else {
              // It's a nested object, recurse
              flatten(value, prefix ? `${prefix}.${key}` : key);
            }
          } else if (Array.isArray(value)) {
            // It's an array of errors - join them
            flattened[prefix ? `${prefix}.${key}` : key] = value.join(', ');
          } else {
            // It's a simple string error
            flattened[prefix ? `${prefix}.${key}` : key] = value;
          }
        }
      };
      
      flatten(this.errors);
      return flattened;
    }
  }
}
</script>

<style scoped>
/* Optional: Add any custom styles here */
</style>