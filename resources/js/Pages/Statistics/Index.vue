<template>
    <Head title="Статистика" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-amber-800 dark:text-gray-200">
                    Статистика занятий
                </h2>
                <div class="flex gap-2">
                    <select v-model="selectedPeriod" @change="updatePeriod" class="text-sm border-amber-300 dark:border-gray-600 focus:border-orange-500 dark:focus:border-indigo-400 focus:ring-orange-500 dark:focus:ring-indigo-400 rounded-md shadow-sm bg-orange-50 dark:bg-gray-800 text-amber-900 dark:text-gray-100">
                        <option value="day">День</option>
                        <option value="week">Неделя</option>
                        <option value="month">Месяц</option>
                        <option value="year">Год</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Общая статистика -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Общее время практики -->
                    <div class="bg-yellow-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-yellow-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-amber-900 dark:text-gray-100">Время практики</h3>
                                    <p class="text-2xl font-bold text-yellow-600 dark:text-blue-400">{{ formatMinutes(currentStats.total_minutes) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Количество сессий -->
                    <div class="bg-red-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Сессии</h3>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ currentStats.sessions_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Текущая серия -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Текущая серия</h3>
                                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ chartData.practice_streak.current_streak }} дней</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Рекордная серия -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Рекордная серия</h3>
                                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ chartData.practice_streak.longest_streak }} дней</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Графики -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- График ежедневной практики -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Практика за последние 30 дней</h3>
                            <div class="h-64">
                                <canvas ref="dailyChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- График типов упражнений -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Типы упражнений</h3>
                            <div class="h-64">
                                <canvas ref="exerciseTypesChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Детальная статистика по периоду -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Детальная статистика за {{ getPeriodLabel(selectedPeriod) }}</h3>
                        
                        <!-- Статистика по дням недели (для недели) -->
                        <div v-if="selectedPeriod === 'week'" class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">По дням недели</h4>
                            <div class="grid grid-cols-7 gap-2">
                                <div v-for="day in currentStats.daily_stats" :key="day.date" class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ getDayName(day.day_name) }}</div>
                                    <div class="bg-blue-100 dark:bg-blue-900 rounded p-2">
                                        <div class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ formatMinutes(day.minutes) }}</div>
                                        <div class="text-xs text-blue-600 dark:text-blue-300">{{ day.sessions }} сессий</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика по неделям месяца (для месяца) -->
                        <div v-if="selectedPeriod === 'month'" class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">По неделям месяца</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="week in currentStats.weekly_stats" :key="week.week_start" class="bg-gray-50 dark:bg-gray-700 rounded p-4">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">
                                        {{ formatDateRange(week.week_start, week.week_end) }}
                                    </div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ formatMinutes(week.minutes) }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ week.sessions }} сессий</div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика по месяцам года (для года) -->
                        <div v-if="selectedPeriod === 'year'" class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">По месяцам года</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                                <div v-for="month in currentStats.monthly_stats" :key="month.month" class="bg-gray-50 dark:bg-gray-700 rounded p-3 text-center">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-1">{{ getMonthName(month.month_name) }}</div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ formatMinutes(month.minutes) }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300">{{ month.sessions }} сессий</div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика по типам упражнений -->
                        <div v-if="chartData.exercise_types.length > 0">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3">По типам упражнений</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div v-for="exercise in chartData.exercise_types" :key="exercise.type" class="bg-gray-50 dark:bg-gray-700 rounded p-4">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">{{ exercise.type_label }}</div>
                                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatMinutes(exercise.minutes) }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ exercise.count }} упражнений</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Chart, registerables } from 'chart.js'

// Регистрируем компоненты Chart.js
Chart.register(...registerables)

interface Statistics {
    day: any
    week: any
    month: any
    year: any
}

interface ChartData {
    daily_practice: any[]
    weekly_practice: any[]
    exercise_types: any[]
    practice_streak: {
        current_streak: number
        longest_streak: number
        total_practice_days: number
    }
}

interface Props {
    statistics: Statistics
    chartData: ChartData
    currentPeriod: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.currentPeriod)
const dailyChart = ref<HTMLCanvasElement>()
const exerciseTypesChart = ref<HTMLCanvasElement>()

// Вычисляемые свойства
const currentStats = computed(() => {
    return props.statistics[selectedPeriod.value as keyof Statistics]
})

// Методы
const formatMinutes = (minutes: number): string => {
    if (minutes === 0) return '0 мин'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    if (hours > 0) {
        return mins > 0 ? `${hours}ч ${mins}м` : `${hours}ч`
    }
    return `${mins}м`
}

const getPeriodLabel = (period: string): string => {
    const labels = {
        day: 'день',
        week: 'неделю',
        month: 'месяц',
        year: 'год'
    }
    return labels[period as keyof typeof labels] || period
}

const getDayName = (dayName: string): string => {
    const days = {
        Monday: 'Пн',
        Tuesday: 'Вт',
        Wednesday: 'Ср',
        Thursday: 'Чт',
        Friday: 'Пт',
        Saturday: 'Сб',
        Sunday: 'Вс'
    }
    return days[dayName as keyof typeof days] || dayName
}

const getMonthName = (monthName: string): string => {
    const months = {
        January: 'Янв',
        February: 'Фев',
        March: 'Мар',
        April: 'Апр',
        May: 'Май',
        June: 'Июн',
        July: 'Июл',
        August: 'Авг',
        September: 'Сен',
        October: 'Окт',
        November: 'Ноя',
        December: 'Дек'
    }
    return months[monthName as keyof typeof months] || monthName
}

const formatDateRange = (start: string, end: string): string => {
    const startDate = new Date(start)
    const endDate = new Date(end)
    return `${startDate.getDate()}-${endDate.getDate()}`
}

const updatePeriod = () => {
    // Здесь можно добавить логику для обновления данных при смене периода
    // Пока просто перерисовываем графики
    nextTick(() => {
        createDailyChart()
        createExerciseTypesChart()
    })
}

const createDailyChart = () => {
    if (!dailyChart.value) return
    
    const ctx = dailyChart.value.getContext('2d')
    if (!ctx) return
    
    // Уничтожаем предыдущий график, если он существует
    const existingChart = Chart.getChart(dailyChart.value)
    if (existingChart) {
        existingChart.destroy()
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.chartData.daily_practice.map(item => {
                const date = new Date(item.date)
                return `${date.getDate()}/${date.getMonth() + 1}`
            }),
            datasets: [{
                label: 'Минуты практики',
                data: props.chartData.daily_practice.map(item => item.minutes),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatMinutes(value as number)
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    })
}

const createExerciseTypesChart = () => {
    if (!exerciseTypesChart.value) return
    
    const ctx = exerciseTypesChart.value.getContext('2d')
    if (!ctx) return
    
    // Уничтожаем предыдущий график, если он существует
    const existingChart = Chart.getChart(exerciseTypesChart.value)
    if (existingChart) {
        existingChart.destroy()
    }
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.chartData.exercise_types.map(item => item.type_label),
            datasets: [{
                data: props.chartData.exercise_types.map(item => item.minutes),
                backgroundColor: [
                    '#3B82F6', // blue
                    '#10B981', // green
                    '#F59E0B', // yellow
                    '#EF4444', // red
                    '#8B5CF6', // purple
                    '#06B6D4', // cyan
                    '#84CC16', // lime
                    '#F97316', // orange
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const minutes = context.parsed
                            return `${context.label}: ${formatMinutes(minutes)}`
                        }
                    }
                }
            }
        }
    })
}

onMounted(() => {
    nextTick(() => {
        createDailyChart()
        createExerciseTypesChart()
    })
})
</script>