<template>
  <div class="calendar-container bg-white dark:bg-black rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
    <div class="calendar-header flex flex-col sm:flex-row justify-between items-center p-4 border-b border-slate-200 dark:border-slate-700">
      <div class="flex items-center space-x-4 mb-4 sm:mb-0">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-100">
          {{ currentDateFormatted }}
          <span v-if="currentView !== 'month'">
            - {{ currentView === 'week' ? 'Week' : formatDate(currentDate) }}
          </span>
        </h2>
        <button @click="goToToday" class="px-3 py-1 text-sm rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300">
          Today
        </button>
      </div>

      <div class="flex items-center space-x-2">
        <button @click="prevPeriod" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
          <ChevronLeft class="w-5 h-5" />
        </button>

        <div class="view-switcher flex bg-slate-100 dark:bg-slate-800 rounded-md p-1">
          <button 
            v-for="view in ['month', 'week', 'day']"
            :key="view"
            @click="changeView(view)"
            :class="[
              'px-3 py-1 text-sm rounded-md capitalize',
              currentView === view 
                ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-slate-100' 
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
            ]"
          >
            {{ view }}
          </button>
        </div>

        <button @click="nextPeriod" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
          <ChevronRight class="w-5 h-5" />
        </button>

        <button @click="emit('refresh')" :disabled="loading" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
          <RefreshCw :class="['w-5 h-5', loading ? 'animate-spin' : '']" />
        </button>
      </div>
    </div>

    <div v-if="loading || error" class="p-4 text-center">
      <div v-if="loading" class="flex items-center justify-center">
        <RefreshCw class="w-6 h-6 animate-spin text-slate-600 dark:text-slate-400" />
      </div>
      <div v-if="error" class="text-red-500 dark:text-red-400">
        {{ error }}
        <button @click="emit('refresh')" class="ml-2 px-3 py-1 text-sm rounded-md bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300">
          Retry
        </button>
      </div>
    </div>

    <!-- Month View -->
    <div v-if="currentView === 'month' && !loading && !error" class="month-view p-4">
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center py-2 text-sm font-medium text-slate-500 dark:text-slate-400">
          {{ day }}
        </div>
      </div>

      <div class="grid grid-cols-7 gap-1">
        <div v-for="i in firstDayOfMonthValue" :key="`prev-${i}`" class="h-24 p-1 border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded" />

        <div 
          v-for="day in daysInMonthValue"
          :key="day"
          @click="handleDateClick(getDateForDay(day))"
          :class="[
            'h-24 p-1 border rounded cursor-pointer transition-colors group border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800',
            isToday(getDateForDay(day)) ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700' : ''
          ]"
        >
          <div class="flex justify-between items-start">
            <span :class="[
              'text-sm px-1 rounded-full',
              isToday(getDateForDay(day)) ? 'bg-blue-500 text-white font-medium' : 'text-slate-700 dark:text-slate-300'
            ]">
              {{ day }}
            </span>
            <button 
              @click.stop="handleAddEvent(getDateForDay(day))"
              class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <Plus class="w-4 h-4" />
            </button>
          </div>

          <div class="mt-1 overflow-y-auto max-h-16">
            <div 
              v-for="event in getEventsForDay(getDateForDay(day))"
              :key="event.id"
              @click.stop="handleEventClick(event)"
              :class="['text-xs p-1 mb-1 rounded truncate cursor-pointer text-white', getEventColorClasses(event)]"
            >
              <div class="flex items-center space-x-1">
                <component :is="getEventIcon(event)" class="w-3 h-3 flex-shrink-0" />
                <span class="truncate">{{ event.title }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Week View -->
    <div v-if="currentView === 'week' && !loading && !error" class="week-view p-4">
      <div class="grid grid-cols-8 gap-1 mb-2">
        <div class="text-center py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Time</div>
        <div v-for="date in weekDates" :key="date.toISOString()" class="text-center py-2 text-sm font-medium text-slate-500 dark:text-slate-400">
          <div :class="isToday(date) ? 'text-blue-600 dark:text-blue-400 font-semibold' : ''">
            {{ date.toLocaleDateString('en-US', { weekday: 'short' }) }}
          </div>
          <div :class="['text-lg', isToday(date) ? 'bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto' : '']">
            {{ date.getDate() }}
          </div>
        </div>
      </div>

      <div class="grid grid-cols-8 gap-1 max-h-96 overflow-y-auto">
        <div class="space-y-12">
          <div v-for="(timeSlot, index) in timeSlots" :key="index" class="text-xs text-slate-500 dark:text-slate-400 h-12 flex items-start">
            {{ formatTime(timeSlot) }}
          </div>
        </div>

        <div 
          v-for="date in weekDates"
          :key="date.toISOString()"
          @click="handleDateClick(date)"
          :class="[
            'relative border-l border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800',
            isToday(date) ? 'bg-blue-50 dark:bg-blue-900/20' : ''
          ]"
          style="min-height: 1440px"
        >
          <button 
            @click.stop="handleAddEvent(date)"
            class="absolute top-2 right-2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity z-10"
          >
            <Plus class="w-4 h-4" />
          </button>

          <div
            v-for="event in getEventsForDay(date)"
            :key="event.id"
            @click.stop="handleEventClick(event)"
            :class="['absolute left-1 right-1 rounded p-1 cursor-pointer text-white text-xs', getEventColorClasses(event)]"
            :style="{
              top: `${getEventPosition(event, date).top}px`,
              height: `${getEventPosition(event, date).height}px`,
              zIndex: 5
            }"
          >
            <div class="flex items-center space-x-1">
              <component :is="getEventIcon(event)" class="w-3 h-3 flex-shrink-0" />
              <div class="truncate">
                <div class="font-medium truncate">{{ event.title }}</div>
                <div class="text-xs opacity-90">
                  {{ getEventPosition(event, date).startTime }} - {{ getEventPosition(event, date).endTime }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-for="(_, index) in timeSlots"
            :key="index"
            class="absolute left-0 right-0 border-t border-slate-100 dark:border-slate-700"
            :style="{ top: `${index * 60}px` }"
          />
        </div>
      </div>
    </div>

    <!-- Day View -->
    <div v-if="currentView === 'day' && !loading && !error" class="day-view p-4">
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
          {{ formatDate(currentDate) }}
          <span v-if="isToday(currentDate)" class="ml-2 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">
            Today
          </span>
        </h3>
      </div>

      <div class="grid grid-cols-2 gap-4 max-h-96 overflow-y-auto">
        <div class="space-y-12">
          <div v-for="(timeSlot, index) in timeSlots" :key="index" class="text-sm text-slate-500 dark:text-slate-400 h-12 flex items-start py-1">
            {{ formatTime(timeSlot) }}
          </div>
        </div>

        <div 
          :class="[
            'relative border-l border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 group',
            isToday(currentDate) ? 'bg-blue-50 dark:bg-blue-900/20' : ''
          ]"
          @click="handleDateClick(currentDate)"
          style="min-height: 1440px"
        >
          <button 
            @click.stop="handleAddEvent(currentDate)"
            class="absolute top-2 right-2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity z-10"
          >
            <Plus class="w-4 h-4" />
          </button>

          <div
            v-for="event in getEventsForDay(currentDate)"
            :key="event.id"
            @click.stop="handleEventClick(event)"
            :class="['absolute left-2 right-2 rounded p-2 cursor-pointer text-white', getEventColorClasses(event)]"
            :style="{
              top: `${getEventPosition(event, currentDate).top}px`,
              height: `${getEventPosition(event, currentDate).height}px`,
              zIndex: 5
            }"
          >
            <div class="flex items-start space-x-2">
              <component :is="getEventIcon(event)" class="w-4 h-4 flex-shrink-0 mt-1" />
              <div class="flex-1 min-w-0">
                <div class="font-medium truncate">{{ event.title }}</div>
                <div class="text-sm opacity-90">
                  {{ getEventPosition(event, currentDate).startTime }} - {{ getEventPosition(event, currentDate).endTime }}
                </div>
              </div>
            </div>
          </div>

          <div
            v-for="(_, index) in timeSlots"
            :key="index"
            class="absolute left-0 right-0 border-t border-slate-100 dark:border-slate-700"
            :style="{ top: `${index * 60}px` }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { 
  Calendar, 
  CheckSquare, 
  Users, 
  CalendarDays, 
  ChevronLeft, 
  ChevronRight, 
  RefreshCw, 
  Plus 
} from 'lucide-vue-next';

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['dateClick', 'eventClick', 'monthChange', 'refresh', 'addEvent']);

const currentView = ref('month');
const currentDate = ref(new Date());
const width = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);

const currentDateFormatted = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const daysInMonthValue = computed(() => {
  return daysInMonth(currentDate.value.getMonth(), currentDate.value.getFullYear());
});

const firstDayOfMonthValue = computed(() => {
  return firstDayOfMonth(currentDate.value.getMonth(), currentDate.value.getFullYear());
});

const weekDates = computed(() => {
  return getWeekDates(currentDate.value);
});

const timeSlots = computed(() => {
  return generateTimeSlots();
});

const handleResize = () => {
  width.value = window.innerWidth;
  if (window.innerWidth < 768 && currentView.value === 'week') {
    currentView.value = 'day';
  }
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  emitMonthChange(currentDate.value, currentView.value);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

watch([currentView, currentDate], () => {
  emitMonthChange(currentDate.value, currentView.value);
});

const emitMonthChange = (date, view) => {
  let start, end;
  if (view === 'month') {
    start = new Date(date.getFullYear(), date.getMonth(), 1);
    end = new Date(date.getFullYear(), date.getMonth() + 1, 0);
  } else if (view === 'week') {
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1);
    start = new Date(date.getFullYear(), date.getMonth(), diff);
    end = new Date(start);
    end.setDate(start.getDate() + 6);
  } else {
    start = new Date(date);
    end = new Date(date);
  }
  emit('monthChange', { startStr: start.toISOString(), endStr: end.toISOString() });
};

const prevPeriod = () => {
  let newDate;
  if (currentView.value === 'month') {
    newDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
  } else if (currentView.value === 'week') {
    newDate = new Date(currentDate.value.getTime() - 7 * 24 * 60 * 60 * 1000);
  } else {
    newDate = new Date(currentDate.value.getTime() - 24 * 60 * 60 * 1000);
  }
  currentDate.value = newDate;
};

const nextPeriod = () => {
  let newDate;
  if (currentView.value === 'month') {
    newDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
  } else if (currentView.value === 'week') {
    newDate = new Date(currentDate.value.getTime() + 7 * 24 * 60 * 60 * 1000);
  } else {
    newDate = new Date(currentDate.value.getTime() + 24 * 60 * 60 * 1000);
  }
  currentDate.value = newDate;
};

const goToToday = () => {
  currentDate.value = new Date();
};

const changeView = (view) => {
  if (width.value < 768 && view === 'week') {
    currentView.value = 'day';
  } else {
    currentView.value = view;
  }
};

const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();

const firstDayOfMonth = (month, year) => new Date(year, month, 1).getDay();

const getWeekDates = (date) => {
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1);
  const weekStart = new Date(date.getFullYear(), date.getMonth(), diff);
  const weekDates = [];
  for (let i = 0; i < 7; i++) {
    const day = new Date(weekStart);
    day.setDate(weekStart.getDate() + i);
    weekDates.push(day);
  }
  return weekDates;
};

const isSameDay = (date1, date2) => {
  return date1.getDate() === date2.getDate() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getFullYear() === date2.getFullYear();
};

const isToday = (date) => {
  const today = new Date();
  return isSameDay(date, today);
};

const formatDate = (date) => {
  return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
};

const formatTime = (date) => {
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const getDateForDay = (day) => {
  return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day);
};

const getEventsForDay = (day) => {
  return props.events.filter(event => {
    const eventStart = new Date(event.start);
    const eventEnd = event.end ? new Date(event.end) : eventStart;

    if (eventStart.getHours() === 0 && eventStart.getMinutes() === 0 && 
        eventEnd.getHours() === 23 && eventEnd.getMinutes() === 59) {
      return day >= new Date(eventStart.getFullYear(), eventStart.getMonth(), eventStart.getDate()) &&
             day <= new Date(eventEnd.getFullYear(), eventEnd.getMonth(), eventEnd.getDate());
    }

    return (day >= eventStart && day <= eventEnd) || 
           isSameDay(day, eventStart) || 
           isSameDay(day, eventEnd);
  });
};

const getEventIcon = (event) => {
  if (event.color === 'teal') return CheckSquare;
  if (event.color === 'green') return Users;
  if (event.color === 'brown') return CalendarDays;
  return Calendar;
};

const getEventColorClasses = (event) => {
  switch (event.color) {
    case 'teal': return 'bg-teal-500 hover:bg-teal-600';
    case 'green': return 'bg-green-500 hover:bg-green-600';
    case 'brown': return 'bg-amber-600 hover:bg-amber-700';
    default: return 'bg-blue-500 hover:bg-blue-600';
  }
};

const generateTimeSlots = () => {
  const slots = [];
  for (let hour = 0; hour < 24; hour++) {
    slots.push(new Date(0, 0, 0, hour, 0));
  }
  return slots;
};

const getEventPosition = (event, dayStart) => {
  const eventStart = new Date(event.start);
  const eventEnd = event.end ? new Date(event.end) : new Date(eventStart.getTime() + 60 * 60 * 1000);

  const startMinutes = eventStart.getHours() * 60 + eventStart.getMinutes();
  const endMinutes = eventEnd.getHours() * 60 + eventEnd.getMinutes();
  const duration = endMinutes - startMinutes;

  return {
    top: (startMinutes / 60) * 60,
    height: Math.max((duration / 60) * 60, 30),
    startTime: formatTime(eventStart),
    endTime: formatTime(eventEnd)
  };
};

const handleDateClick = (date) => {
  emit('dateClick', { dateStr: date.toISOString() });
};

const handleEventClick = (event) => {
  emit('eventClick', event);
};

const handleAddEvent = (date) => {
  emit('addEvent', { start: date });
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>