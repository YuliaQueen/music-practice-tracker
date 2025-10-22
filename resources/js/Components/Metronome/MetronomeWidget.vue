<template>
    <div class="metronome-widget bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ t('metronome.title') }}
            </h3>
            <button
                @click="toggleCollapse"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                <svg
                    class="w-5 h-5 transition-transform"
                    :class="{ 'rotate-180': !isCollapsed }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </button>
        </div>

        <div v-show="!isCollapsed">
            <!-- BPM Display -->
            <div class="text-center mb-6">
                <div class="text-5xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ bpm }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ t('metronome.bpm') }}
                </div>
            </div>

            <!-- Visual Beat Indicator -->
            <div class="flex justify-center items-center gap-2 mb-6 h-16">
                <div
                    v-for="beat in beatsPerMeasure"
                    :key="beat"
                    class="beat-indicator"
                    :class="{
                        'beat-active': currentBeat === beat - 1 && isPlaying,
                        'beat-accent': beat === 1 && accentFirstBeat,
                    }"
                >
                    {{ beat }}
                </div>
            </div>

            <!-- BPM Slider -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <button
                        @click="decrementBpm(5)"
                        class="btn-small"
                        :disabled="bpm <= 30"
                    >
                        -5
                    </button>
                    <button
                        @click="decrementBpm(1)"
                        class="btn-small"
                        :disabled="bpm <= 30"
                    >
                        -1
                    </button>
                    <button
                        @click="tapTempo"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                        {{ t('metronome.tap') }}
                    </button>
                    <button
                        @click="incrementBpm(1)"
                        class="btn-small"
                        :disabled="bpm >= 300"
                    >
                        +1
                    </button>
                    <button
                        @click="incrementBpm(5)"
                        class="btn-small"
                        :disabled="bpm >= 300"
                    >
                        +5
                    </button>
                </div>
                <input
                    type="range"
                    :value="bpm"
                    @input="setBpm(parseInt(($event.target as HTMLInputElement).value))"
                    min="30"
                    max="300"
                    step="1"
                    class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
                />
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span>30</span>
                    <span>300</span>
                </div>
            </div>

            <!-- Controls Row 1: Time Signature & Volume -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <!-- Time Signature -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ t('metronome.timeSignature') }}
                    </label>
                    <select
                        :value="timeSignature"
                        @change="setTimeSignature(($event.target as HTMLSelectElement).value as TimeSignature)"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                    >
                        <option value="2/4">2/4</option>
                        <option value="3/4">3/4</option>
                        <option value="4/4">4/4</option>
                        <option value="5/4">5/4</option>
                        <option value="6/8">6/8</option>
                        <option value="7/8">7/8</option>
                        <option value="9/8">9/8</option>
                        <option value="12/8">12/8</option>
                    </select>
                </div>

                <!-- Volume -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ t('metronome.volume') }}
                    </label>
                    <input
                        type="range"
                        :value="volume"
                        @input="setVolume(parseFloat(($event.target as HTMLInputElement).value))"
                        min="0"
                        max="1"
                        step="0.05"
                        class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
                    />
                </div>
            </div>

            <!-- Controls Row 2: Sound Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ t('metronome.soundType') }}
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        @click="setSoundType('click')"
                        class="sound-type-btn"
                        :class="{ 'active': soundType === 'click' }"
                    >
                        {{ t('metronome.sounds.click') }}
                    </button>
                    <button
                        @click="setSoundType('beep')"
                        class="sound-type-btn"
                        :class="{ 'active': soundType === 'beep' }"
                    >
                        {{ t('metronome.sounds.beep') }}
                    </button>
                </div>
            </div>

            <!-- Accent First Beat Toggle -->
            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="accentFirstBeat"
                        @change="toggleAccent"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ t('metronome.accentFirstBeat') }}
                    </span>
                </label>
            </div>

            <!-- Auto-Increment BPM -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <label class="flex items-center cursor-pointer mb-3">
                    <input
                        type="checkbox"
                        v-model="autoIncrement"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <span class="ml-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ t('metronome.autoIncrement') }}
                    </span>
                </label>

                <div v-if="autoIncrement" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Interval -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                {{ t('metronome.intervalSeconds') }}
                            </label>
                            <input
                                type="number"
                                v-model.number="autoIncrementInterval"
                                min="10"
                                max="600"
                                step="10"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                            />
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                {{ t('metronome.incrementAmount') }}
                            </label>
                            <input
                                type="number"
                                v-model.number="autoIncrementAmount"
                                min="1"
                                max="20"
                                step="1"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                            />
                        </div>
                    </div>

                    <!-- Target BPM -->
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="useTargetBpm"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700 dark:border-gray-600"
                            />
                            <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                {{ t('metronome.useTargetBpm') }}
                            </span>
                        </label>

                        <div v-if="useTargetBpm">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                {{ t('metronome.targetBpm') }}
                            </label>
                            <input
                                type="number"
                                v-model.number="targetBpm"
                                :min="bpm + 1"
                                max="300"
                                step="5"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                            />
                        </div>
                    </div>

                    <!-- Timer indicator -->
                    <div v-if="isPlaying && timeUntilIncrement > 0" class="text-center py-2 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                            {{ t('metronome.nextIncrementIn') }}
                        </div>
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ Math.floor(timeUntilIncrement / 60) }}:{{ String(timeUntilIncrement % 60).padStart(2, '0') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Play/Stop Button -->
            <button
                @click="toggle"
                class="w-full py-3 px-4 rounded-lg font-semibold text-white transition-all transform hover:scale-105 active:scale-95"
                :class="isPlaying
                    ? 'bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700'
                    : 'bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700'
                "
            >
                <span class="flex items-center justify-center gap-2">
                    <svg
                        v-if="!isPlaying"
                        class="w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg
                        v-else
                        class="w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                    </svg>
                    {{ isPlaying ? t('metronome.stop') : t('metronome.start') }}
                </span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useMetronome, TimeSignature } from '@/composables/useMetronome';
import { useSimpleI18n } from '@/composables/useSimpleI18n';

const { t } = useSimpleI18n();

const {
    isPlaying,
    currentBeat,
    bpm,
    timeSignature,
    volume,
    soundType,
    accentFirstBeat,
    autoIncrement,
    autoIncrementInterval,
    autoIncrementAmount,
    useTargetBpm,
    targetBpm,
    timeUntilIncrement,
    beatsPerMeasure,
    toggle,
    setBpm,
    setTimeSignature,
    setVolume,
    setSoundType,
    toggleAccent,
    tapTempo,
    incrementBpm,
    decrementBpm,
} = useMetronome();

const isCollapsed = ref(false);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
};
</script>

<style scoped>
.beat-indicator {
    @apply w-12 h-12 flex items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-semibold transition-all duration-150;
}

.beat-active {
    @apply bg-blue-500 dark:bg-blue-600 border-blue-500 dark:border-blue-600 text-white scale-110 shadow-lg;
}

.beat-accent {
    @apply border-orange-400 dark:border-orange-500;
}

.beat-active.beat-accent {
    @apply bg-orange-500 dark:bg-orange-600 border-orange-500 dark:border-orange-600;
}

.btn-small {
    @apply px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors;
}

.sound-type-btn {
    @apply px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors;
}

.sound-type-btn.active {
    @apply bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700;
}

/* Custom slider styling */
.slider::-webkit-slider-thumb {
    @apply appearance-none w-4 h-4 bg-blue-500 dark:bg-blue-600 rounded-full cursor-pointer hover:bg-blue-600 dark:hover:bg-blue-700;
}

.slider::-moz-range-thumb {
    @apply w-4 h-4 bg-blue-500 dark:bg-blue-600 rounded-full cursor-pointer border-0 hover:bg-blue-600 dark:hover:bg-blue-700;
}
</style>
