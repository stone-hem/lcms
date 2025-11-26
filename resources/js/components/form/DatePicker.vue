<template>
  <div class="flex flex-col space-y-1.5">
    <label 
      v-if="label" 
      :for="id" 
      class="text-xs font-medium text-gray-600 dark:text-gray-200 light:text-gray-800 transition-colors"
      :class="{ 'text-gray-500 dark:text-gray-500 light:text-gray-400': disabled }"
    >
      {{ label }} <span class="text-red-500">{{ required ? '*' : '' }}</span>
    </label>

    <div class="relative">
      <div
        @click="togglePicker"
        :class="[
          'flex items-center justify-between px-3 py-2 rounded-md border cursor-pointer transition-all duration-200',
          'bg-white dark:bg-gray-800 light:bg-white text-gray-900 dark:text-gray-100 light:text-gray-900 border-gray-200 dark:border-gray-700 light:border-gray-300',
          'hover:border-gray-500 dark:hover:border-gray-600 light:hover:border-gray-400',
          disabled ? 'opacity-50 cursor-not-allowed' : '',
          isOpen ? 'ring-1 ring-blue-500 border-transparent' : '',
          error ? 'border-red-500' : ''
        ]"
      >
        <span :class="selectedValue ? 'text-gray-800 dark:text-gray-100 light:text-gray-900' : 'text-gray-500 dark:text-gray-500 light:text-gray-400'">
          {{ displayValue || placeholder || getDefaultPlaceholder }}
        </span>
        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 light:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="mode === 'time'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>

      <button
        v-if="clearable && hasValue && !disabled"
        type="button"
        @click.stop="clear"
        class="absolute right-8 top-1/2 -translate-y-1/2 p-1 rounded bg-gray-200 dark:bg-gray-700 light:bg-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 light:hover:bg-gray-300 text-gray-600 dark:text-gray-300 light:text-gray-600 hover:text-gray-800 dark:hover:text-white light:hover:text-gray-800 transition-all duration-200"
        title="Clear"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Dropdown Picker -->
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-1 bg-white dark:bg-gray-800 light:bg-white border border-gray-300 dark:border-gray-700 light:border-gray-300 rounded-md shadow-xl max-h-80 overflow-y-auto',
          mode === 'time' ? 'w-56' : 'w-72'
        ]"
        @click.stop
      >
        <div class="p-3">
          <!-- Calendar (date and datetime modes) -->
          <div v-if="mode !== 'time'" class="mb-3">
            <div class="flex items-center justify-between mb-2">
              <button
                type="button"
                @click="previousMonth"
                class="p-0.5 hover:bg-gray-100 dark:hover:bg-gray-700 light:hover:bg-gray-100 rounded transition-colors"
              >
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 light:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <div class="flex gap-1">
                <select
                  v-model="currentMonth"
                  class="px-2 py-0.5 bg-gray-50 dark:bg-gray-700 light:bg-gray-50 border border-gray-300 dark:border-gray-600 light:border-gray-300 rounded text-xs text-gray-800 dark:text-gray-200 light:text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <option v-for="(month, idx) in months" :key="idx" :value="idx">
                    {{ month }}
                  </option>
                </select>

                <select
                  v-model="currentYear"
                  class="px-2 py-0.5 bg-gray-50 dark:bg-gray-700 light:bg-gray-50 border border-gray-300 dark:border-gray-600 light:border-gray-300 rounded text-xs text-gray-800 dark:text-gray-200 light:text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                  <option v-for="year in years" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
              </div>

              <button
                type="button"
                @click="nextMonth"
                class="p-0.5 hover:bg-gray-100 dark:hover:bg-gray-700 light:hover:bg-gray-100 rounded transition-colors"
              >
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 light:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
              <div v-for="day in weekDays" :key="day" class="text-xs text-gray-600 dark:text-gray-500 light:text-gray-600 font-medium py-0.5">
                {{ day }}
              </div>
            </div>

            <div class="grid grid-cols-7 gap-0.5">
              <button
                v-for="day in calendarDays"
                :key="day.key"
                type="button"
                @click="selectDate(day)"
                :disabled="!day.isCurrentMonth || isDateDisabled(day)"
                :class="[
                  'aspect-square p-0.5 text-xs rounded transition-all duration-200',
                  day.isCurrentMonth ? 'text-gray-900 dark:text-gray-200 light:text-gray-900' : 'text-gray-400 dark:text-gray-600 light:text-gray-400',
                  day.isToday ? 'bg-blue-100 dark:bg-blue-900 light:bg-blue-100 bg-opacity-30' : '',
                  day.isSelected ? 'bg-blue-600 text-white font-semibold' : 'hover:bg-gray-100 dark:hover:bg-gray-700 light:hover:bg-gray-100',
                  !day.isCurrentMonth || isDateDisabled(day) ? 'opacity-30 cursor-not-allowed' : ''
                ]"
              >
                {{ day.date }}
              </button>
            </div>
          </div>

          <!-- Time Picker (time and datetime modes) -->
          <div v-if="mode !== 'date'" :class="mode === 'datetime' ? 'pt-3 border-t border-gray-300 dark:border-gray-700 light:border-gray-300' : ''">
            <div class="flex items-center gap-1 mb-2">
              <svg class="w-3 h-3 text-gray-600 dark:text-gray-400 light:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-xs text-gray-700 dark:text-gray-300 light:text-gray-700">{{ mode === 'time' ? 'Select Time' : 'Time' }}</span>
            </div>

            <div class="flex items-center gap-1">
              <div class="flex-1">
                <input
                  v-model.number="selectedHour"
                  type="number"
                  min="0"
                  max="23"
                  placeholder="HH"
                  class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-700 light:bg-gray-50 border border-gray-300 dark:border-gray-600 light:border-gray-300 rounded text-center text-xs text-gray-800 dark:text-gray-200 light:text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  @input="validateHour"
                />
                <div class="text-xs text-gray-600 dark:text-gray-500 light:text-gray-600 text-center mt-0.5">Hour</div>
              </div>

              <span class="text-lg text-gray-600 dark:text-gray-500 light:text-gray-600 pb-4">:</span>

              <div class="flex-1">
                <input
                  v-model.number="selectedMinute"
                  type="number"
                  min="0"
                  max="59"
                  placeholder="MM"
                  class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-700 light:bg-gray-50 border border-gray-300 dark:border-gray-600 light:border-gray-300 rounded text-center text-xs text-gray-800 dark:text-gray-200 light:text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  @input="validateMinute"
                />
                <div class="text-xs text-gray-600 dark:text-gray-500 light:text-gray-600 text-center mt-0.5">Minute</div>
              </div>
            </div>

            <div class="flex gap-1 mt-3">
              <button
                type="button"
                @click="setNow"
                class="flex-1 px-2 py-1.5 bg-gray-100 dark:bg-gray-700 light:bg-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 light:hover:bg-gray-200 text-gray-800 dark:text-gray-200 light:text-gray-800 rounded text-xs transition-colors"
              >
                Now
              </button>
              <button
                type="button"
                @click="applyValue"
                class="flex-1 px-2 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition-colors"
              >
                Apply
              </button>
            </div>
          </div>

          <!-- Date only mode - Apply button -->
          <div v-if="mode === 'date'" class="mt-3 pt-3 border-t border-gray-300 dark:border-gray-700 light:border-gray-300">
            <button
              type="button"
              @click="applyValue"
              class="w-full px-2 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition-colors"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="hint && !error" class="text-xs text-gray-600 dark:text-gray-400 light:text-gray-600">{{ hint }}</p>
    <p v-if="error" class="text-xs text-red-400">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

type Mode = 'date' | 'time' | 'datetime'

interface Props {
  modelValue?: string | Date | null
  mode?: Mode
  utc?: boolean
  id?: string
  name?: string
  label?: string
  placeholder?: string
  hint?: string
  disabled?: boolean
  min?: string | Date
  max?: string | Date
  clearable?: boolean
  validateRange?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  mode: 'datetime',
  utc: false,
  id: () => `dtp-${Math.random().toString(36).slice(2, 8)}`,
  name: '',
  label: '',
  placeholder: '',
  hint: '',
  disabled: false,
  min: undefined,
  max: undefined,
  clearable: true,
  validateRange: true,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
}>()

const isOpen = ref(false)
const selectedDate = ref<Date | null>(null)
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedHour = ref<number>(0)
const selectedMinute = ref<number>(0)
const error = ref('')

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const years = computed(() => {
  const current = new Date().getFullYear()
  return Array.from({ length: 21 }, (_, i) => current - 10 + i)
})

const selectedValue = computed(() => {
  if (props.mode === 'time') {
    return selectedHour.value !== null && selectedMinute.value !== null
  }
  return selectedDate.value !== null
})

const hasValue = computed(() => selectedValue.value)

const getDefaultPlaceholder = computed(() => {
  switch (props.mode) {
    case 'date': return 'Select date'
    case 'time': return 'Select time'
    case 'datetime': return 'Select date and time'
    default: return 'Select'
  }
})

const displayValue = computed(() => {
  if (props.mode === 'time') {
    if (selectedHour.value === null || selectedMinute.value === null) return ''
    const hours = String(selectedHour.value).padStart(2, '0')
    const minutes = String(selectedMinute.value).padStart(2, '0')
    return `${hours}:${minutes}`
  }
  
  if (!selectedDate.value) return ''
  
  const date = selectedDate.value
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  
  if (props.mode === 'date') {
    return `${year}-${month}-${day}`
  }
  
  // datetime
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day} ${hours}:${minutes}`
})

interface CalendarDay {
  date: number
  month: number
  year: number
  isCurrentMonth: boolean
  isToday: boolean
  isSelected: boolean
  key: string
}

const calendarDays = computed((): CalendarDay[] => {
  const days: CalendarDay[] = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const prevLastDay = new Date(currentYear.value, currentMonth.value, 0)
  
  const firstDayOfWeek = firstDay.getDay()
  const lastDate = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()
  
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  
  // Previous month days
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = prevLastDate - i
    days.push({
      date,
      month: currentMonth.value - 1,
      year: currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      key: `prev-${date}`
    })
  }
  
  // Current month days
  for (let date = 1; date <= lastDate; date++) {
    const dayDate = new Date(currentYear.value, currentMonth.value, date)
    dayDate.setHours(0, 0, 0, 0)
    
    const isToday = dayDate.getTime() === today.getTime()
    const isSelected = selectedDate.value ? 
      dayDate.getTime() === new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), selectedDate.value.getDate()).getTime() : 
      false
    
    days.push({
      date,
      month: currentMonth.value,
      year: currentYear.value,
      isCurrentMonth: true,
      isToday,
      isSelected,
      key: `current-${date}`
    })
  }
  
  // Next month days
  const remainingDays = 42 - days.length
  for (let date = 1; date <= remainingDays; date++) {
    days.push({
      date,
      month: currentMonth.value + 1,
      year: currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      key: `next-${date}`
    })
  }
  
  return days
})

const parseDate = (value: string | Date | null | undefined): Date | null => {
  if (!value) return null
  
  try {
    if (value instanceof Date) {
      return isNaN(value.getTime()) ? null : value
    }
    
    const date = new Date(value)
    return isNaN(date.getTime()) ? null : date
  } catch {
    return null
  }
}

const parseTime = (value: string | null): { hour: number, minute: number } | null => {
  if (!value) return null
  
  try {
    const match = value.match(/^(\d{1,2}):(\d{2})/)
    if (match) {
      const hour = parseInt(match[1], 10)
      const minute = parseInt(match[2], 10)
      if (hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
        return { hour, minute }
      }
    }
  } catch {
    return null
  }
  
  return null
}

const isDateDisabled = (day: CalendarDay): boolean => {
  if (!props.validateRange) return false
  
  const date = new Date(day.year, day.month, day.date)
  
  if (props.min) {
    const minDate = parseDate(props.min)
    if (minDate) {
      minDate.setHours(0, 0, 0, 0)
      if (date < minDate) return true
    }
  }
  
  if (props.max) {
    const maxDate = parseDate(props.max)
    if (maxDate) {
      maxDate.setHours(23, 59, 59, 999)
      if (date > maxDate) return true
    }
  }
  
  return false
}

const togglePicker = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

const selectDate = (day: CalendarDay) => {
  if (!day.isCurrentMonth || isDateDisabled(day)) return
  
  const newDate = new Date(day.year, day.month, day.date, selectedHour.value || 0, selectedMinute.value || 0)
  selectedDate.value = newDate
  
  // Auto-apply for date-only mode
  if (props.mode === 'date') {
    applyValue()
  }
}

const validateHour = () => {
  if (selectedHour.value < 0) selectedHour.value = 0
  if (selectedHour.value > 23) selectedHour.value = 23
}

const validateMinute = () => {
  if (selectedMinute.value < 0) selectedMinute.value = 0
  if (selectedMinute.value > 59) selectedMinute.value = 59
}

const setNow = () => {
  const now = new Date()
  
  if (props.mode !== 'time') {
    selectedDate.value = now
    currentMonth.value = now.getMonth()
    currentYear.value = now.getFullYear()
  }
  
  if (props.mode !== 'date') {
    selectedHour.value = now.getHours()
    selectedMinute.value = now.getMinutes()
  }
}

const applyValue = () => {
  if (props.mode === 'time') {
    // Time only mode
    if (selectedHour.value === null || selectedMinute.value === null) {
      error.value = 'Please select a time'
      return
    }
    
    const hours = String(selectedHour.value).padStart(2, '0')
    const minutes = String(selectedMinute.value).padStart(2, '0')
    emit('update:modelValue', `${hours}:${minutes}`)
    
  } else if (props.mode === 'date') {
    // Date only mode
    if (!selectedDate.value) {
      error.value = 'Please select a date'
      return
    }
    
    const year = selectedDate.value.getFullYear()
    const month = String(selectedDate.value.getMonth() + 1).padStart(2, '0')
    const day = String(selectedDate.value.getDate()).padStart(2, '0')
    
    if (props.utc) {
      const utcDate = new Date(Date.UTC(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), selectedDate.value.getDate()))
      emit('update:modelValue', utcDate.toISOString().split('T')[0])
    } else {
      emit('update:modelValue', `${year}-${month}-${day}`)
    }
    
  } else {
    // DateTime mode
    if (!selectedDate.value) {
      error.value = 'Please select a date'
      return
    }
    
    const finalDate = new Date(
      selectedDate.value.getFullYear(),
      selectedDate.value.getMonth(),
      selectedDate.value.getDate(),
      selectedHour.value || 0,
      selectedMinute.value || 0,
      0,
      0
    )
    
    selectedDate.value = finalDate
    
    if (props.utc) {
      emit('update:modelValue', finalDate.toISOString())
    } else {
      const year = finalDate.getFullYear()
      const month = String(finalDate.getMonth() + 1).padStart(2, '0')
      const day = String(finalDate.getDate()).padStart(2, '0')
      const hours = String(finalDate.getHours()).padStart(2, '0')
      const minutes = String(finalDate.getMinutes()).padStart(2, '0')
      emit('update:modelValue', `${year}-${month}-${day}T${hours}:${minutes}`)
    }
  }
  
  isOpen.value = false
  error.value = ''
}

const clear = () => {
  selectedDate.value = null
  selectedHour.value = 0
  selectedMinute.value = 0
  error.value = ''
  emit('update:modelValue', null)
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    isOpen.value = false
  }
}

watch(() => props.modelValue, (value) => {
  if (!value) {
    selectedDate.value = null
    selectedHour.value = 0
    selectedMinute.value = 0
    return
  }
  
  if (props.mode === 'time') {
    const time = parseTime(value as string)
    if (time) {
      selectedHour.value = time.hour
      selectedMinute.value = time.minute
    }
  } else {
    const date = parseDate(value)
    if (date) {
      selectedDate.value = date
      selectedHour.value = date.getHours()
      selectedMinute.value = date.getMinutes()
      currentMonth.value = date.getMonth()
      currentYear.value = date.getFullYear()
    }
  }
}, { immediate: true })

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>