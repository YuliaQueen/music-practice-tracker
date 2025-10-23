<template>
    <div class="compact-metronome bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <button
            @click="isCollapsed = !isCollapsed"
            class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors rounded-t-lg"
        >
            <div class="flex items-center gap-3">
                <span class="text-xl">🎵</span>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Метроном
                </h3>
                <span v-if="isCollapsed" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ bpm }} BPM {{ isPlaying ? '▶' : '' }}
                </span>
            </div>
            <svg
                class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                :class="{ 'rotate-180': !isCollapsed }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Compact Content -->
        <div v-show="!isCollapsed" class="px-4 pb-4 space-y-4">
            <!-- Main Controls Row -->
            <div class="flex items-center gap-3">
                <!-- BPM Display & Input -->
                <div class="flex items-center gap-2">
                    <button
                        @click="decrementBpm(5)"
                        class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                        :disabled="bpm <= 30"
                    >
                        −
                    </button>
                    <input
                        type="number"
                        :value="bpm"
                        @input="setBpm(parseInt(($event.target as HTMLInputElement).value))"
                        min="30"
                        max="300"
                        class="w-16 px-2 py-1 text-center text-sm font-semibold bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                        @click="incrementBpm(5)"
                        class="w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                        :disabled="bpm >= 300"
                    >
                        +
                    </button>
                    <span class="text-xs text-gray-500 dark:text-gray-400">BPM</span>
                </div>

                <!-- Time Signature -->
                <select
                    :value="timeSignature"
                    @change="setTimeSignature(($event.target as HTMLSelectElement).value as TimeSignature)"
                    class="px-2 py-1 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-gray-100"
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

                <!-- Play/Stop Button -->
                <button
                    @click="toggle"
                    class="flex-shrink-0 px-4 py-2 flex items-center gap-2 text-sm font-medium text-white rounded transition-colors"
                    :class="isPlaying ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'"
                >
                    <span v-if="isPlaying">⏸</span>
                    <span v-else>▶</span>
                    <span class="hidden sm:inline">{{ isPlaying ? 'Стоп' : 'Старт' }}</span>
                </button>

                <!-- Tap Button -->
                <button
                    @click="tapTempo"
                    class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Tap
                </button>
            </div>

            <!-- Beat Indicators (compact) -->
            <div class="flex items-center gap-1">
                <div
                    v-for="beat in beatsPerMeasure"
                    :key="beat"
                    class="flex-1 h-2 rounded-full transition-all duration-100"
                    :class="{
                        'bg-green-500': currentBeat === beat - 1 && isPlaying && beat === 1 && accentFirstBeat,
                        'bg-blue-500': currentBeat === beat - 1 && isPlaying && (beat !== 1 || !accentFirstBeat),
                        'bg-gray-300 dark:bg-gray-600': currentBeat !== beat - 1 || !isPlaying
                    }"
                ></div>
            </div>

            <!-- Tempo Presets (compact) -->
            <div class="flex gap-1 overflow-x-auto">
                <button
                    v-for="preset in tempoPresets"
                    :key="preset.name"
                    @click="applyTempoPreset(preset.value as any)"
                    class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors whitespace-nowrap"
                    :title="`${preset.bpm} BPM`"
                >
                    {{ preset.name }}
                </button>
            </div>

            <!-- Volume Control (compact) -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600 dark:text-gray-400">🔊</span>
                <input
                    type="range"
                    :value="volume"
                    @input="setVolume(parseInt(($event.target as HTMLInputElement).value))"
                    min="0"
                    max="100"
                    step="5"
                    class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                />
                <span class="text-xs text-gray-500 dark:text-gray-400 w-8">{{ volume }}%</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useMetronome, TimeSignature } from '@/composables/useMetronome';

interface Props {
    initiallyCollapsed?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    initiallyCollapsed: true
});

const {
    isPlaying,
    currentBeat,
    bpm,
    timeSignature,
    volume,
    accentFirstBeat,
    beatsPerMeasure,
    toggle,
    setBpm,
    setTimeSignature,
    setVolume,
    tapTempo,
    incrementBpm,
    decrementBpm,
    applyTempoPreset,
} = useMetronome();

const isCollapsed = ref(props.initiallyCollapsed);

const tempoPresets = [
    { name: 'Largo', value: 'largo', bpm: 50 },
    { name: 'Adagio', value: 'adagio', bpm: 71 },
    { name: 'Andante', value: 'andante', bpm: 92 },
    { name: 'Moderato', value: 'moderato', bpm: 114 },
    { name: 'Allegro', value: 'allegro', bpm: 144 },
    { name: 'Presto', value: 'presto', bpm: 184 },
];
</script>
