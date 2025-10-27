<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                Создать занятие
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6 text-primary-900 dark:text-neutral-100">
                        <form @submit.prevent="submit">
                            <!-- Основная информация -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100 mb-4">Основная информация</h3>

                                <!-- Выбор режима сессии -->
                                <div class="mb-6 p-4 bg-neutral-50 rounded-lg dark:bg-neutral-700/50 border border-neutral-200 dark:border-neutral-600">
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                                        Выберите режим занятия
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <!-- Стандартная сессия -->
                                        <button
                                            type="button"
                                            @click="sessionMode = 'standard'"
                                            :class="[
                                                'flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all',
                                                sessionMode === 'standard'
                                                    ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-200 dark:border-accent-400 dark:bg-accent-900/20 dark:ring-accent-400'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:hover:border-neutral-500 dark:hover:bg-neutral-700'
                                            ]"
                                        >
                                            <span class="text-3xl mb-2">📚</span>
                                            <span class="font-semibold text-neutral-900 dark:text-neutral-100">Стандартная сессия</span>
                                            <span class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 text-center">
                                                Добавьте упражнения вручную и настройте структуру
                                            </span>
                                        </button>

                                        <!-- Pomodoro-сессия -->
                                        <button
                                            type="button"
                                            @click="sessionMode = 'pomodoro'"
                                            :class="[
                                                'flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all',
                                                sessionMode === 'pomodoro'
                                                    ? 'border-danger-500 bg-danger-50 ring-2 ring-danger-200 dark:border-danger-400 dark:bg-danger-900/20 dark:ring-danger-400'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:hover:border-neutral-500 dark:hover:bg-neutral-700'
                                            ]"
                                        >
                                            <span class="text-3xl mb-2">🍅</span>
                                            <span class="font-semibold text-neutral-900 dark:text-neutral-100">Pomodoro-сессия</span>
                                            <span class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 text-center">
                                                Автоматическое чередование работы и отдыха
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Уведомление о добавленном упражнении -->
                                <div v-if="exerciseData" class="mb-4 p-4 bg-primary-100 border border-primary-200 rounded-lg dark:bg-success-900/20 dark:border-success-800">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-primary-800 dark:text-success-200">
                                                Упражнение "{{ exerciseData.title }}" добавлено в занятие
                                            </h4>
                                            <p class="text-sm text-primary-700 dark:text-success-300 mt-1">
                                                Вы можете добавить еще упражнения или изменить параметры этого упражнения ниже.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <InputLabel for="title" value="Название занятия" />
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    @click="generateSimpleTitle"
                                                    class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded hover:bg-neutral-200 transition-colors dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600"
                                                    title="Генерировать название с датой и временем"
                                                >
                                                    🕐 Дата и время
                                                </button>
                                                <button
                                                    v-if="sessionMode === 'standard'"
                                                    type="button"
                                                    @click="generateAutoTitle"
                                                    class="text-xs px-2 py-1 bg-accent-100 text-accent-600 rounded hover:bg-accent-200 transition-colors dark:bg-accent-900/50 dark:text-accent-300 dark:hover:bg-accent-800/50"
                                                    title="Генерировать название с упражнениями, датой и временем"
                                                >
                                                    🎯 С упражнениями
                                                </button>
                                            </div>
                                        </div>
                                        <TextInput
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.title" />
                                    </div>

                                    <div>
                                        <InputLabel for="template_id" value="Шаблон (опционально)" />
                                        <select
                                            id="template_id"
                                            v-model="form.template_id"
                                            class="mt-1 block w-full border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-md shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 dark:focus:border-accent-400 dark:focus:ring-accent-400"
                                        >
                                            <option value="">Без шаблона</option>
                                        <option
                                            v-for="template in templates"
                                            :key="template.id"
                                            :value="template.id.toString()"
                                        >
                                                {{ template.name }} ({{ template.total_duration }} мин)
                                            </option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.template_id" />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="description" value="Описание" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-md shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 dark:focus:border-accent-400 dark:focus:ring-accent-400"
                                        rows="3"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Автопереход между упражнениями (только для стандартного режима) -->
                                <div v-if="sessionMode === 'standard'" class="mt-4">
                                    <label class="flex items-center">
                                        <input
                                            id="auto_advance"
                                            v-model="form.auto_advance"
                                            type="checkbox"
                                            class="rounded border-neutral-300 text-accent-600 shadow-sm focus:ring-accent-500 dark:border-neutral-600 dark:bg-neutral-700 dark:focus:ring-accent-400 dark:focus:ring-offset-neutral-800"
                                        />
                                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-400">
                                            🚀 <strong>Автопереход:</strong> Автоматически начинать следующее упражнение после завершения текущего
                                        </span>
                                    </label>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400 ml-6">
                                        Упражнения будут запускаться одно за другим без дополнительных действий. Вы сможете приостановить или пропустить упражнение в любой момент.
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.auto_advance" />
                                </div>
                            </div>

                            <!-- Настройки Pomodoro (только для Pomodoro режима) -->
                            <div v-if="sessionMode === 'pomodoro'" class="mb-6">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Настройки Pomodoro</h3>

                                <div class="p-4 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h4 class="font-medium text-neutral-900 dark:text-neutral-100">Текущие настройки</h4>
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                                Количество циклов: {{ pomodoroSettings.totalCycles }} • Общая длительность: {{ pomodoro.calculateTotalMinutes(pomodoroSettings) }} минут
                                            </p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                                {{ pomodoroPreview }}
                                            </p>
                                        </div>
                                        <SecondaryButton
                                            type="button"
                                            @click="showPomodoroModal = true"
                                            class="text-sm"
                                        >
                                            ⚙️ Настроить
                                        </SecondaryButton>
                                    </div>

                                    <!-- Превью слотов -->
                                    <div v-if="pomodoroSlots.length > 0" class="mt-4">
                                        <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            Превью расписания ({{ pomodoroTotalCycles }} рабочих циклов):
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="(slot, index) in pomodoroSlots.slice(0, 8)"
                                                :key="index"
                                                :class="[
                                                    'text-xs px-2 py-1 rounded border',
                                                    pomodoroGetSlotColorClass(slot.type)
                                                ]"
                                            >
                                                {{ pomodoroGetSlotIcon(slot.type) }} {{ slot.duration }}м
                                            </span>
                                            <span v-if="pomodoroSlots.length > 8" class="text-xs text-neutral-500 dark:text-neutral-400 self-center">
                                                +{{ pomodoroSlots.length - 8 }} еще...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Блоки упражнений (только для стандартного режима) -->
                            <div v-if="sessionMode === 'standard'" class="mb-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Упражнения</h3>
                                    <div class="flex space-x-2">
                                        <SecondaryButton
                                            v-if="previousExercises.length > 0"
                                            type="button"
                                            @click="showExercisesList = !showExercisesList"
                                            class="text-sm"
                                        >
                                            📚 Из библиотеки упражнений
                                        </SecondaryButton>
                                        <PrimaryButton
                                            type="button"
                                            @click="addBlock"
                                            class="text-sm"
                                        >
                                            + Добавить упражнение
                                        </PrimaryButton>
                                    </div>
                                </div>

                                <!-- Список упражнений из библиотеки -->
                                <div v-if="showExercisesList && previousExercises.length > 0" class="mb-6 p-4 bg-neutral-50 rounded-lg dark:bg-neutral-700">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="font-medium text-neutral-900 dark:text-neutral-100">Выберите упражнения из библиотеки</h4>
                                        <div class="flex space-x-2">
                                            <SecondaryButton
                                                type="button"
                                                @click="clearExerciseSelection"
                                                class="text-sm"
                                                :disabled="selectedExercises.size === 0"
                                            >
                                                Очистить выбор
                                            </SecondaryButton>
                                            <PrimaryButton
                                                type="button"
                                                @click="addSelectedExercises"
                                                class="text-sm"
                                                :disabled="selectedExercises.size === 0"
                                            >
                                                Добавить выбранные ({{ selectedExercises.size }})
                                            </PrimaryButton>
                                            <DangerButton
                                                type="button"
                                                @click="closeExercisesList"
                                                class="text-sm"
                                            >
                                                ✕ Закрыть
                                            </DangerButton>
                                        </div>
                                    </div>
                                    
                                    <!-- Поиск и сортировка упражнений -->
                                    <div class="mb-4 space-y-3">
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <div class="flex-1">
                                                <TextInput
                                                    v-model="exerciseSearchQuery"
                                                    type="text"
                                                    placeholder="Поиск по названию, описанию или типу..."
                                                    class="w-full"
                                                />
                                            </div>
                                            <div class="sm:w-48">
                                                <select
                                                    v-model="exerciseSortBy"
                                                    class="w-full border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-md shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 dark:focus:border-accent-400 dark:focus:ring-accent-400"
                                                >
                                                    <option value="usage">По популярности</option>
                                                    <option value="name">По названию</option>
                                                    <option value="duration">По длительности</option>
                                                </select>
                                            </div>
                                        </div>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Найдено: {{ filteredExercises.length }} из {{ previousExercises.length }} упражнений
                                        </p>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto">
                                        <div
                                            v-for="exercise in filteredExercises"
                                            :key="exercise.title"
                                            @click="toggleExerciseSelection(exercise.title)"
                                            :class="[
                                                'p-3 border rounded-lg cursor-pointer transition-all duration-200',
                                                selectedExercises.has(exercise.title)
                                                    ? 'border-accent-500 bg-accent-50 ring-2 ring-accent-200 dark:border-accent-400 dark:bg-accent-900/20 dark:ring-accent-400'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-800 dark:hover:border-neutral-500 dark:hover:bg-neutral-700'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h5 class="font-medium text-neutral-900 dark:text-neutral-100 text-sm">{{ exercise.title }}</h5>
                                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ exercise.description || 'Без описания' }}</p>
                                                    <div class="flex items-center space-x-2 mt-2">
                                                        <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded dark:bg-neutral-600 dark:text-neutral-300">
                                                            {{ getTypeLabel(exercise.type) }}
                                                        </span>
                                                        <span class="text-xs px-2 py-1 bg-accent-100 text-accent-600 rounded dark:bg-accent-900/50 dark:text-accent-300">
                                                            {{ exercise.duration }} мин
                                                        </span>
                                                        <span v-if="exercise.usage_count > 0" class="text-xs px-2 py-1 bg-success-100 text-success-600 rounded dark:bg-success-900/50 dark:text-success-300">
                                                            {{ exercise.usage_count }} раз
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <div v-if="selectedExercises.has(exercise.title)" class="w-5 h-5 bg-accent-500 rounded-full flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div v-else class="w-5 h-5 border-2 border-neutral-300 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.blocks.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                    Добавьте упражнения для вашего занятия
                                </div>

                                <div v-else class="mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg">
                                    <div class="flex items-center text-xs sm:text-sm text-accent-700 dark:text-accent-300">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>
                                            <strong>Совет:</strong> Перетащите упражнения чтобы изменить порядок
                                        </span>
                                    </div>
                                </div>

                                <draggable
                                    v-if="form.blocks.length > 0"
                                    v-model="form.blocks"
                                    item-key="title"
                                    tag="div"
                                    class="space-y-2"
                                    ghost-class="dragging-ghost"
                                    chosen-class="dragging-chosen"
                                    :animation="200"
                                >
                                    <template #item="{ element: block, index }">
                                        <div
                                            class="border border-neutral-200 rounded-lg p-3 dark:border-neutral-600 dark:bg-neutral-700 cursor-move hover:shadow-md hover:border-accent-300 dark:hover:border-accent-600 transition-all"
                                        >
                                            <!-- Компактная строка с inline редактированием -->
                                            <div class="flex items-center gap-3">
                                                <!-- Drag handle -->
                                                <div class="flex-shrink-0 text-primary-400 dark:text-neutral-500 hover:text-accent-500 dark:hover:text-accent-400 transition-colors" title="Перетащите">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16" />
                                                    </svg>
                                                </div>

                                                <!-- Номер -->
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center">
                                                    <span class="text-sm font-bold text-accent-600 dark:text-accent-400">{{ index + 1 }}</span>
                                                </div>

                                                <!-- Название (inline редактирование) -->
                                                <div class="flex-1 min-w-0">
                                                    <input
                                                        v-model="block.title"
                                                        type="text"
                                                        placeholder="Название упражнения"
                                                        class="w-full px-2 py-1 text-sm border-0 border-b border-transparent hover:border-neutral-300 focus:border-accent-500 focus:ring-0 bg-transparent dark:text-neutral-100 dark:hover:border-neutral-600 dark:focus:border-accent-400 transition-colors"
                                                        required
                                                    />
                                                </div>

                                                <!-- Тип (компактный select) -->
                                                <div class="flex-shrink-0">
                                                    <select
                                                        v-model="block.type"
                                                        class="px-2 py-1 text-xs border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
                                                        required
                                                    >
                                                        <option value="warmup">🔥 Разминка</option>
                                                        <option value="technique">⚡ Техника</option>
                                                        <option value="repertoire">🎵 Репертуар</option>
                                                        <option value="improvisation">🎨 Импровизация</option>
                                                        <option value="sight_reading">👀 Чтение</option>
                                                        <option value="theory">📚 Теория</option>
                                                        <option value="break">☕ Перерыв</option>
                                                        <option value="custom">⭐ Другое</option>
                                                    </select>
                                                </div>

                                                <!-- Длительность (компактный input) -->
                                                <div class="flex-shrink-0 flex items-center gap-1">
                                                    <input
                                                        v-model.number="block.duration"
                                                        type="number"
                                                        min="1"
                                                        placeholder="15"
                                                        class="w-16 px-2 py-1 text-sm text-center border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
                                                        required
                                                    />
                                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">мин</span>
                                                </div>

                                                <!-- Кнопка удалить -->
                                                <button
                                                    type="button"
                                                    @click="removeBlock(index)"
                                                    class="flex-shrink-0 p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                    title="Удалить"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Описание (опциональное, скрываемое) -->
                                            <div v-if="block.description || block === expandedBlock" class="mt-2 ml-14">
                                                <textarea
                                                    v-model="block.description"
                                                    placeholder="Описание (опционально)..."
                                                    class="w-full px-2 py-1 text-xs border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100"
                                                    rows="1"
                                                    @focus="expandedBlock = block"
                                                    @blur="() => { if (!block.description) expandedBlock = null }"
                                                ></textarea>
                                            </div>
                                            <button
                                                v-else
                                                type="button"
                                                @click="expandedBlock = block"
                                                class="mt-2 ml-14 text-xs text-neutral-500 hover:text-accent-600 dark:text-neutral-400 dark:hover:text-accent-400 transition-colors"
                                            >
                                                + Добавить описание
                                            </button>
                                        </div>
                                    </template>
                                </draggable>

                                <InputError class="mt-2" :message="form.errors.blocks" />
                            </div>

                            <!-- Итого -->
                            <div v-if="totalDuration > 0" class="mb-6 p-4 bg-neutral-50 rounded-lg dark:bg-neutral-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Общая длительность:</span>
                                    <span class="text-lg font-bold text-accent-600 dark:text-accent-400">{{ totalDuration }} минут</span>
                                </div>
                            </div>

                            <!-- Кнопки -->
                            <div class="flex items-center justify-end space-x-4">
                                <SecondaryButton
                                    type="button"
                                    @click="$inertia.visit(route('sessions.index'))"
                                >
                                    Отмена
                                </SecondaryButton>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Создать занятие
                                </PrimaryButton>
                            </div>
                        </form>

                        <!-- Модальное окно настроек Pomodoro -->
                        <PomodoroSettingsModal
                            :show="showPomodoroModal"
                            :settings="pomodoroSettings"
                            @close="showPomodoroModal = false"
                            @save="handlePomodoroSettingsSave"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import draggable from 'vuedraggable'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import PomodoroSettingsModal from '@/Components/Session/PomodoroSettingsModal.vue'
import { usePomodoro, type PomodoroSettings } from '@/composables/usePomodoro'

interface Template {
    id: number
    name: string
    total_duration: number
    blocks: Array<{
        title: string
        type: string
        duration: number
    }>
}

interface Block {
    title: string
    description: string
    type: string
    duration: number | string
}

interface PreviousExercise {
    title: string
    description: string
    type: string
    duration: number
    usage_count: number
}

interface ExerciseData {
    id: number
    title: string
    type: string
    duration: number
    description?: string
}

interface Props {
    templates: Template[]
    previousExercises: PreviousExercise[]
    exerciseData?: ExerciseData
}

const props = defineProps<Props>()

// Режим сессии
const sessionMode = ref<'standard' | 'pomodoro'>('standard')

const form = useForm({
    title: '',
    description: '',
    template_id: null as number | null,
    auto_advance: false,
    blocks: [] as Block[],
})

// Pomodoro composable и состояние
const pomodoro = usePomodoro()
const showPomodoroModal = ref(false)
const pomodoroSettings = ref<PomodoroSettings>({
    totalCycles: 4,
    workDuration: 25,
    shortBreak: 5,
    longBreak: 15,
    cyclesBeforeLongBreak: 4,
})

// Вычисляемые свойства для Pomodoro
const pomodoroSlots = computed(() => pomodoro.calculateSlots(pomodoroSettings.value))
const pomodoroTotalCycles = computed(() => pomodoro.calculateTotalCycles(pomodoroSlots.value))
const pomodoroPreview = computed(() => pomodoro.getShortPreview(pomodoroSlots.value))
const pomodoroGetSlotIcon = (type: 'custom' | 'break') => pomodoro.getSlotIcon(type)
const pomodoroGetSlotColorClass = (type: 'custom' | 'break') => pomodoro.getSlotColorClass(type)

// Обработчик сохранения настроек Pomodoro
const handlePomodoroSettingsSave = (settings: PomodoroSettings) => {
    pomodoroSettings.value = { ...settings }
}

// Состояние для управления списком упражнений
const showExercisesList = ref(false)
const selectedExercises = ref<Set<string>>(new Set())
const exerciseSearchQuery = ref('')
const exerciseSortBy = ref<'name' | 'usage' | 'duration'>('usage')
const expandedBlock = ref<Block | null>(null)

const totalDuration = computed(() => {
    return form.blocks.reduce((total, block) => {
        const duration = typeof block.duration === 'string' ? parseInt(block.duration) || 0 : block.duration || 0
        return total + duration
    }, 0)
})

// Фильтрация и сортировка упражнений
const filteredExercises = computed(() => {
    let exercises = props.previousExercises
    
    // Фильтрация по поисковому запросу
    if (exerciseSearchQuery.value.trim()) {
        const query = exerciseSearchQuery.value.toLowerCase()
        exercises = exercises.filter(exercise => 
            exercise.title.toLowerCase().includes(query) ||
            (exercise.description && exercise.description.toLowerCase().includes(query)) ||
            exercise.type.toLowerCase().includes(query)
        )
    }
    
    // Сортировка
    return exercises.sort((a, b) => {
        switch (exerciseSortBy.value) {
            case 'name':
                return a.title.localeCompare(b.title)
            case 'usage':
                return b.usage_count - a.usage_count
            case 'duration':
                return a.duration - b.duration
            default:
                return 0
        }
    })
})

const addBlock = () => {
    form.blocks.push({
        title: '',
        description: '',
        type: 'custom',
        duration: 5,
    })
    
    // Автоматически генерируем название, если поле пустое
    if (!form.title.trim()) {
        generateSimpleTitle()
    }
}

const removeBlock = (index: number) => {
    form.blocks.splice(index, 1)
}

// Функции для работы с упражнениями
const toggleExerciseSelection = (exerciseTitle: string) => {
    if (selectedExercises.value.has(exerciseTitle)) {
        selectedExercises.value.delete(exerciseTitle)
    } else {
        selectedExercises.value.add(exerciseTitle)
    }
}

const addSelectedExercises = () => {
    const exercisesToAdd = props.previousExercises.filter(exercise => 
        selectedExercises.value.has(exercise.title)
    )
    
    exercisesToAdd.forEach(exercise => {
        form.blocks.push({
            title: exercise.title,
            description: exercise.description,
            type: exercise.type,
            duration: typeof exercise.duration === 'string' ? parseInt(exercise.duration) : exercise.duration,
        })
    })
    
    // Автоматически генерируем название, если поле пустое или содержит только дату
    if (!form.title.trim() || form.title.includes('Занятие') && !form.title.includes(' - ')) {
        generateAutoTitle()
    }
    
    // Очищаем выбор и закрываем список
    selectedExercises.value.clear()
    showExercisesList.value = false
}

const clearExerciseSelection = () => {
    selectedExercises.value.clear()
}

const closeExercisesList = () => {
    showExercisesList.value = false
    exerciseSearchQuery.value = ''
    selectedExercises.value.clear()
}

// Функция для получения читаемого названия типа упражнения
const getTypeLabel = (type: string) => {
    const typeLabels = {
        warmup: '🔥 Разминка',
        technique: '⚡ Техника',
        repertoire: '🎵 Репертуар',
        improvisation: '🎨 Импровизация',
        sight_reading: '👀 Чтение с листа',
        theory: '📚 Теория',
        break: '☕ Перерыв',
        custom: '⭐ Пользовательский',
    }
    return typeLabels[type as keyof typeof typeLabels] || type
}

// Функция для форматирования даты
const formatDate = (date: Date) => {
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }
    return date.toLocaleDateString('ru-RU', options)
}

// Функция для форматирования даты и времени
const formatDateTime = (date: Date) => {
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }
    return date.toLocaleDateString('ru-RU', options)
}

// Функция для автоматической генерации названия занятия
const generateAutoTitle = () => {
    const now = new Date()
    const dateTime = formatDateTime(now)
    
    // Получаем упражнения, исключая разминку и перерывы
    const nonWarmupExercises = form.blocks.filter(block => 
        block.type !== 'warmup' && block.type !== 'break' && block.title.trim()
    )
    
    if (nonWarmupExercises.length === 0) {
        // Если нет основных упражнений, используем только дату и время
        form.title = `Занятие ${dateTime}`
        return
    }
    
    // Берем первые 2-3 упражнения для названия
    const exerciseTitles = nonWarmupExercises
        .slice(0, 3)
        .map(block => block.title)
        .join(', ')
    
    // Формируем название
    if (nonWarmupExercises.length <= 3) {
        form.title = `${exerciseTitles} - ${dateTime}`
    } else {
        form.title = `${exerciseTitles} и др. - ${dateTime}`
    }
}

// Функция для генерации краткого названия (только дата и время)
const generateSimpleTitle = () => {
    const now = new Date()
    const dateTime = formatDateTime(now)
    form.title = `Занятие ${dateTime}`
}

const submit = () => {
    if (sessionMode.value === 'pomodoro') {
        // Для Pomodoro отправляем специальные поля
        const pomodoroForm = useForm({
            session_mode: 'pomodoro',
            title: form.title,
            description: form.description,
            pomodoro_total_minutes: pomodoro.calculateTotalMinutes(pomodoroSettings.value),
            pomodoro_work_duration: pomodoroSettings.value.workDuration,
            pomodoro_short_break: pomodoroSettings.value.shortBreak,
            pomodoro_long_break: pomodoroSettings.value.longBreak,
            pomodoro_cycles_before_long_break: pomodoroSettings.value.cyclesBeforeLongBreak,
        })

        pomodoroForm.post(route('sessions.store'), {
            onSuccess: () => {
                // Успешно создано
            },
        })
    } else {
        // Стандартная сессия
        form.post(route('sessions.store'), {
            onSuccess: () => {
                // Успешно создано
            },
        })
    }
}

// Загрузить шаблон при выборе
const loadTemplate = (templateId: number | null) => {
    if (!templateId) {
        form.blocks = []
        return
    }

    const template = props.templates.find(t => t.id === templateId)
    if (template) {
        form.title = template.name
        form.blocks = template.blocks.map(block => ({
            title: block.title,
            description: '',
            type: block.type,
            duration: typeof block.duration === 'string' ? parseInt(block.duration) : block.duration,
        }))
    }
}

// Отслеживать изменения template_id
const templateId = computed({
    get: () => form.template_id,
    set: (value) => {
        form.template_id = value
        loadTemplate(value)
    }
})

// Автоматическое обновление названия при изменении упражнений
watch(() => form.blocks, () => {
    // Автоматически обновляем название, если оно было сгенерировано автоматически
    if (form.title.includes('Занятие') || form.title.includes(' - ')) {
        generateAutoTitle()
    }
}, { deep: true })

// Автоматически добавляем упражнение, если данные переданы
if (props.exerciseData) {
    form.blocks.push({
        title: props.exerciseData.title,
        description: props.exerciseData.description || '',
        type: props.exerciseData.type,
        duration: typeof props.exerciseData.duration === 'string' ? parseInt(props.exerciseData.duration) : props.exerciseData.duration,
    })
    
    // Автоматически генерируем название
    if (!form.title.trim()) {
        generateAutoTitle()
    }
}
</script>

<style scoped>
/* Стили для drag & drop */
.dragging-ghost {
    opacity: 0.6 !important;
    transform: scale(1.05) !important;
    cursor: grabbing !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2) !important;
}

.dragging-chosen {
    opacity: 0.5 !important;
    border: 2px dashed #cbd5e1 !important;
    background: #f8fafc !important;
}

:global(.dark) .dragging-chosen {
    background: #0f172a !important;
    border-color: #334155 !important;
}
</style>