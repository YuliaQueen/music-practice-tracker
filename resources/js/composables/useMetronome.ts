import { ref, computed, onUnmounted, Ref } from 'vue';
import MetronomeAudioEngine from '@/utils/metronomeAudioEngine';

export type TimeSignature = '2/4' | '3/4' | '4/4' | '5/4' | '6/8' | '7/8' | '9/8' | '12/8';
export type SoundType = 'click' | 'beep' | 'wood';
export type Subdivision = 'none' | 'eighth' | 'triplet' | 'sixteenth';
export type CountIn = 0 | 1 | 2 | 4; // number of bars
export type TempoPreset = 'custom' | 'largo' | 'adagio' | 'andante' | 'moderato' | 'allegro' | 'presto';

interface MetronomeSettings {
    bpm: number;
    timeSignature: TimeSignature;
    volume: number;
    soundType: SoundType;
    accentFirstBeat: boolean;
    subdivision: Subdivision;
    countIn: CountIn;
    muteMode: boolean;
    muteBars: number; // bars to mute after playing
    playBars: number; // bars to play before muting
    autoIncrement: boolean;
    autoIncrementInterval: number; // seconds
    autoIncrementAmount: number; // BPM to add
    useTargetBpm: boolean;
    targetBpm: number; // target BPM to reach
    speedTrainer: boolean;
    speedTrainerStartBpm: number;
    speedTrainerEndBpm: number;
    speedTrainerStepSize: number;
    speedTrainerBarsPerTempo: number;
    speedTrainerCycle: boolean; // Loop back to start
}

const SETTINGS_KEY = 'metronome_settings';

export function useMetronome() {
    // Audio engine instance
    const audioEngine = new MetronomeAudioEngine();

    // State
    const isPlaying = ref(false);
    const currentBeat = ref(0);
    const bpm = ref(120);
    const timeSignature = ref<TimeSignature>('4/4');
    const volume = ref(0.5);
    const soundType = ref<SoundType>('click');
    const accentFirstBeat = ref(true);
    const subdivision = ref<Subdivision>('none');
    const countIn = ref<CountIn>(0);
    const isCountingIn = ref(false);
    const countInBeatsRemaining = ref(0);

    // Mute mode state
    const muteMode = ref(false);
    const muteBars = ref(2);
    const playBars = ref(4);
    const barsPlayed = ref(0);
    const isMuted = ref(false);

    // Auto-increment state
    const autoIncrement = ref(false);
    const autoIncrementInterval = ref(60); // seconds (default 1 minute)
    const autoIncrementAmount = ref(5); // BPM (default +5)
    const useTargetBpm = ref(false);
    const targetBpm = ref(180); // default target
    const timeUntilIncrement = ref(0); // seconds remaining
    const autoIncrementStartTime = ref<number | null>(null);

    // Speed Trainer state
    const speedTrainer = ref(false);
    const speedTrainerStartBpm = ref(60);
    const speedTrainerEndBpm = ref(120);
    const speedTrainerStepSize = ref(10);
    const speedTrainerBarsPerTempo = ref(4);
    const speedTrainerCycle = ref(true);
    const speedTrainerCurrentBpm = ref(60);
    const speedTrainerBarsRemaining = ref(4);

    // Timing
    let timerID: number | null = null;
    let autoIncrementTimerID: number | null = null;

    // Load settings from localStorage
    const loadSettings = (): void => {
        const savedSettings = localStorage.getItem(SETTINGS_KEY);
        if (savedSettings) {
            try {
                const settings: MetronomeSettings = JSON.parse(savedSettings);
                bpm.value = settings.bpm || 120;
                timeSignature.value = settings.timeSignature || '4/4';
                volume.value = settings.volume !== undefined ? settings.volume : 0.5;
                // Remove wood sound - if it was saved, default to click
                soundType.value = (settings.soundType === 'wood') ? 'click' : (settings.soundType || 'click');
                accentFirstBeat.value = settings.accentFirstBeat !== undefined ? settings.accentFirstBeat : true;
                subdivision.value = settings.subdivision || 'none';
                countIn.value = settings.countIn || 0;
                muteMode.value = settings.muteMode || false;
                muteBars.value = settings.muteBars || 2;
                playBars.value = settings.playBars || 4;
                autoIncrement.value = settings.autoIncrement || false;
                autoIncrementInterval.value = settings.autoIncrementInterval || 60;
                autoIncrementAmount.value = settings.autoIncrementAmount || 5;
                useTargetBpm.value = settings.useTargetBpm || false;
                targetBpm.value = settings.targetBpm || 180;
                speedTrainer.value = settings.speedTrainer || false;
                speedTrainerStartBpm.value = settings.speedTrainerStartBpm || 60;
                speedTrainerEndBpm.value = settings.speedTrainerEndBpm || 120;
                speedTrainerStepSize.value = settings.speedTrainerStepSize || 10;
                speedTrainerBarsPerTempo.value = settings.speedTrainerBarsPerTempo || 4;
                speedTrainerCycle.value = settings.speedTrainerCycle !== undefined ? settings.speedTrainerCycle : true;
            } catch (e) {
                console.error('Failed to load metronome settings:', e);
            }
        }
    };

    // Save settings to localStorage
    const saveSettings = (): void => {
        const settings: MetronomeSettings = {
            bpm: bpm.value,
            timeSignature: timeSignature.value,
            volume: volume.value,
            soundType: soundType.value,
            accentFirstBeat: accentFirstBeat.value,
            subdivision: subdivision.value,
            countIn: countIn.value,
            muteMode: muteMode.value,
            muteBars: muteBars.value,
            playBars: playBars.value,
            autoIncrement: autoIncrement.value,
            autoIncrementInterval: autoIncrementInterval.value,
            autoIncrementAmount: autoIncrementAmount.value,
            useTargetBpm: useTargetBpm.value,
            targetBpm: targetBpm.value,
            speedTrainer: speedTrainer.value,
            speedTrainerStartBpm: speedTrainerStartBpm.value,
            speedTrainerEndBpm: speedTrainerEndBpm.value,
            speedTrainerStepSize: speedTrainerStepSize.value,
            speedTrainerBarsPerTempo: speedTrainerBarsPerTempo.value,
            speedTrainerCycle: speedTrainerCycle.value,
        };
        localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
    };

    // Computed properties
    const beatsPerMeasure = computed(() => {
        const sig = timeSignature.value;
        return parseInt(sig.split('/')[0]);
    });

    const noteValue = computed(() => {
        const sig = timeSignature.value;
        return parseInt(sig.split('/')[1]);
    });

    const secondsPerBeat = computed(() => {
        // Always use the straightforward calculation for metronome
        return 60.0 / bpm.value;
    });

    const displayBeatsPerMeasure = computed(() => {
        // Always show the actual number of beats from time signature
        return beatsPerMeasure.value;
    });

    /**
     * Play sound based on selected sound type
     */
    const playSound = (time: number, isAccent: boolean): void => {
        const vol = volume.value;

        switch (soundType.value) {
            case 'beep':
                audioEngine.playBeep(time, isAccent, vol);
                break;
            case 'wood':
                audioEngine.playWoodBlock(time, isAccent, vol);
                break;
            case 'click':
            default:
                audioEngine.playClick(time, isAccent, vol);
                break;
        }
    };

    // Internal beat counter for scheduling
    let schedulerBeatCounter = 0;

    /**
     * Get number of subdivisions per beat
     */
    const getSubdivisionCount = (): number => {
        switch (subdivision.value) {
            case 'eighth': return 2;
            case 'triplet': return 3;
            case 'sixteenth': return 4;
            default: return 1;
        }
    };

    /**
     * Schedule note to be played with subdivisions and mute mode
     */
    const scheduleNote = (beatNumber: number, time: number): void => {
        // Handle count-in
        if (isCountingIn.value) {
            // Always play sound during count-in (with accent on first beat)
            const isAccent = beatNumber === 0;
            playSound(time, isAccent);

            // Update count-in remaining
            if (beatNumber === 0) {
                countInBeatsRemaining.value--;
                if (countInBeatsRemaining.value <= 0) {
                    isCountingIn.value = false;
                }
            }

            // Update visual indicator
            const visualDelay = Math.max(0, (time - audioEngine.getCurrentTime()) * 1000 - 50);
            setTimeout(() => {
                currentBeat.value = beatNumber;
            }, visualDelay);
            return;
        }

        // Handle speed trainer (on first beat of measure)
        if (speedTrainer.value && beatNumber === 0) {
            speedTrainerBarsRemaining.value--;

            if (speedTrainerBarsRemaining.value <= 0) {
                // Time to change tempo
                const nextBpm = speedTrainerCurrentBpm.value + speedTrainerStepSize.value;

                if (nextBpm > speedTrainerEndBpm.value) {
                    // Reached end tempo
                    if (speedTrainerCycle.value) {
                        // Cycle back to start
                        speedTrainerCurrentBpm.value = speedTrainerStartBpm.value;
                        bpm.value = speedTrainerStartBpm.value;
                    } else {
                        // Stay at end tempo
                        speedTrainerCurrentBpm.value = speedTrainerEndBpm.value;
                        bpm.value = speedTrainerEndBpm.value;
                    }
                } else {
                    // Increase tempo
                    speedTrainerCurrentBpm.value = nextBpm;
                    bpm.value = nextBpm;
                }

                // Reset bars remaining
                speedTrainerBarsRemaining.value = speedTrainerBarsPerTempo.value;
            }
        }

        // Track bars for mute mode (on first beat of measure)
        if (muteMode.value && beatNumber === 0) {
            barsPlayed.value++;
            const cycleLength = playBars.value + muteBars.value;
            const positionInCycle = barsPlayed.value % cycleLength;

            // Check if we should be muted
            isMuted.value = positionInCycle > playBars.value || positionInCycle === 0;
        }

        // Determine if this beat should be accented
        const isAccent = accentFirstBeat.value && beatNumber === 0;

        // Schedule the main beat sound (unless muted)
        if (!isMuted.value) {
            playSound(time, isAccent);
        }

        // Schedule subdivision sounds if enabled (and not muted)
        if (subdivision.value !== 'none' && !isMuted.value) {
            const subdivisionCount = getSubdivisionCount();
            const subdivisionInterval = secondsPerBeat.value / subdivisionCount;

            // Schedule subdivision clicks (skip the first one as it's the main beat)
            for (let i = 1; i < subdivisionCount; i++) {
                const subdivTime = time + (subdivisionInterval * i);
                // Subdivision sounds are quieter (60% of main volume)
                const subdivVolume = volume.value * 0.6;

                switch (soundType.value) {
                    case 'beep':
                        audioEngine.playBeep(subdivTime, false, subdivVolume);
                        break;
                    case 'wood':
                        audioEngine.playWoodBlock(subdivTime, false, subdivVolume);
                        break;
                    case 'click':
                    default:
                        audioEngine.playClick(subdivTime, false, subdivVolume);
                        break;
                }
            }
        }

        // Update visual indicator (in sync with audio)
        // We schedule the visual update slightly before the audio for better perceived sync
        const visualDelay = Math.max(0, (time - audioEngine.getCurrentTime()) * 1000 - 50);
        setTimeout(() => {
            currentBeat.value = beatNumber;
        }, visualDelay);
    };

    /**
     * Scheduler function - looks ahead and schedules notes
     */
    const scheduler = (): void => {
        if (!isPlaying.value) return;

        const currentTime = audioEngine.getCurrentTime();
        const scheduleAheadTime = audioEngine.getScheduleAheadTime();

        // While there are notes that need to be scheduled
        let nextNoteTime = audioEngine.getNextNoteTime();
        let iterationCount = 0;
        const maxIterations = 10; // Защита от бесконечного цикла

        while (nextNoteTime < currentTime + scheduleAheadTime && iterationCount < maxIterations) {
            // Calculate which beat we're on (0-based)
            const beatInMeasure = schedulerBeatCounter % displayBeatsPerMeasure.value;

            // Schedule this note
            scheduleNote(beatInMeasure, nextNoteTime);

            // Advance to next beat
            nextNoteTime = nextNoteTime + secondsPerBeat.value;
            audioEngine.setNextNoteTime(nextNoteTime);

            // Increment beat counter
            schedulerBeatCounter++;

            iterationCount++;
        }

        // Schedule next scheduler call
        if (isPlaying.value) {
            timerID = window.setTimeout(scheduler, audioEngine.getLookahead());
        }
    };

    /**
     * Start auto-increment timer
     */
    const startAutoIncrement = (): void => {
        if (!autoIncrement.value || !isPlaying.value) return;

        autoIncrementStartTime.value = Date.now();
        timeUntilIncrement.value = autoIncrementInterval.value;

        // Update timer every 100ms
        const updateTimer = () => {
            if (!isPlaying.value || !autoIncrement.value) {
                stopAutoIncrement();
                return;
            }

            const elapsed = Math.floor((Date.now() - (autoIncrementStartTime.value || 0)) / 1000);
            const remaining = autoIncrementInterval.value - elapsed;

            if (remaining <= 0) {
                // Time to increment!
                const maxBpm = useTargetBpm.value ? targetBpm.value : 300;
                const newBpm = Math.min(maxBpm, bpm.value + autoIncrementAmount.value);

                // Check if we've reached the target
                if (bpm.value >= maxBpm) {
                    // Target reached, stop auto-increment
                    stopAutoIncrement();
                    return;
                }

                bpm.value = newBpm;
                saveSettings();

                // If we've just reached the target, stop
                if (newBpm >= maxBpm) {
                    stopAutoIncrement();
                    return;
                }

                // Reset timer for next increment
                autoIncrementStartTime.value = Date.now();
                timeUntilIncrement.value = autoIncrementInterval.value;
            } else {
                timeUntilIncrement.value = remaining;
            }

            // Schedule next update
            if (isPlaying.value && autoIncrement.value) {
                autoIncrementTimerID = window.setTimeout(updateTimer, 100);
            }
        };

        updateTimer();
    };

    /**
     * Stop auto-increment timer
     */
    const stopAutoIncrement = (): void => {
        if (autoIncrementTimerID !== null) {
            clearTimeout(autoIncrementTimerID);
            autoIncrementTimerID = null;
        }
        autoIncrementStartTime.value = null;
        timeUntilIncrement.value = 0;
    };

    /**
     * Start the metronome
     */
    const start = async (): Promise<void> => {
        if (isPlaying.value) return;

        // Initialize and resume audio context
        audioEngine.init();
        await audioEngine.resume();

        // Reset state
        currentBeat.value = 0;
        schedulerBeatCounter = 0;
        barsPlayed.value = 0;
        isMuted.value = false;

        // Initialize count-in if enabled
        if (countIn.value > 0) {
            isCountingIn.value = true;
            countInBeatsRemaining.value = countIn.value * displayBeatsPerMeasure.value;
        } else {
            isCountingIn.value = false;
        }

        // Initialize speed trainer if enabled
        if (speedTrainer.value) {
            speedTrainerCurrentBpm.value = speedTrainerStartBpm.value;
            speedTrainerBarsRemaining.value = speedTrainerBarsPerTempo.value;
            bpm.value = speedTrainerStartBpm.value;
        }

        audioEngine.setNextNoteTime(audioEngine.getCurrentTime());

        // Start scheduler
        isPlaying.value = true;
        scheduler();

        // Start auto-increment if enabled
        if (autoIncrement.value) {
            startAutoIncrement();
        }
    };

    /**
     * Stop the metronome
     */
    const stop = (): void => {
        if (!isPlaying.value) return;

        isPlaying.value = false;
        currentBeat.value = 0;
        isCountingIn.value = false;
        countInBeatsRemaining.value = 0;
        barsPlayed.value = 0;
        isMuted.value = false;

        // Reset speed trainer state
        speedTrainerBarsRemaining.value = speedTrainerBarsPerTempo.value;

        if (timerID !== null) {
            clearTimeout(timerID);
            timerID = null;
        }

        // Stop auto-increment timer
        stopAutoIncrement();
    };

    /**
     * Toggle play/pause
     */
    const toggle = async (): Promise<void> => {
        if (isPlaying.value) {
            stop();
        } else {
            await start();
        }
    };

    /**
     * Set BPM (with validation)
     */
    const setBpm = (value: number): void => {
        const validatedBpm = Math.max(30, Math.min(300, value));
        bpm.value = validatedBpm;
        saveSettings();
    };

    /**
     * Set time signature
     */
    const setTimeSignature = (value: TimeSignature): void => {
        const wasPlaying = isPlaying.value;
        if (wasPlaying) stop();

        timeSignature.value = value;
        currentBeat.value = 0;
        saveSettings();

        if (wasPlaying) {
            setTimeout(() => start(), 100);
        }
    };

    /**
     * Set volume (0-1)
     */
    const setVolume = (value: number): void => {
        volume.value = Math.max(0, Math.min(1, value));
        saveSettings();
    };

    /**
     * Set sound type
     */
    const setSoundType = (value: SoundType): void => {
        soundType.value = value;
        saveSettings();
    };

    /**
     * Toggle accent on first beat
     */
    const toggleAccent = (): void => {
        accentFirstBeat.value = !accentFirstBeat.value;
        saveSettings();
    };

    /**
     * Tap tempo - call this function on each tap
     */
    let tapTimes: number[] = [];
    const tapTempo = (): void => {
        const now = Date.now();
        tapTimes.push(now);

        // Keep only last 4 taps
        if (tapTimes.length > 4) {
            tapTimes.shift();
        }

        // Need at least 2 taps to calculate tempo
        if (tapTimes.length >= 2) {
            // Calculate average interval
            let totalInterval = 0;
            for (let i = 1; i < tapTimes.length; i++) {
                totalInterval += tapTimes[i] - tapTimes[i - 1];
            }
            const avgInterval = totalInterval / (tapTimes.length - 1);

            // Convert to BPM
            const calculatedBpm = Math.round(60000 / avgInterval);
            setBpm(calculatedBpm);
        }

        // Reset tap times after 2 seconds of inactivity
        setTimeout(() => {
            if (tapTimes.length > 0 && Date.now() - tapTimes[tapTimes.length - 1] > 2000) {
                tapTimes = [];
            }
        }, 2000);
    };

    /**
     * Increment BPM
     */
    const incrementBpm = (amount: number = 1): void => {
        setBpm(bpm.value + amount);
    };

    /**
     * Decrement BPM
     */
    const decrementBpm = (amount: number = 1): void => {
        setBpm(bpm.value - amount);
    };

    /**
     * Set subdivision
     */
    const setSubdivision = (value: Subdivision): void => {
        subdivision.value = value;
        saveSettings();
    };

    /**
     * Set count-in
     */
    const setCountIn = (value: CountIn): void => {
        countIn.value = value;
        saveSettings();
    };

    /**
     * Set mute mode
     */
    const setMuteMode = (enabled: boolean): void => {
        muteMode.value = enabled;
        saveSettings();
    };

    /**
     * Set play bars (how many bars to play before muting)
     */
    const setPlayBars = (value: number): void => {
        playBars.value = Math.max(1, Math.min(16, value)); // 1-16 bars
        saveSettings();
    };

    /**
     * Set mute bars (how many bars to mute after playing)
     */
    const setMuteBars = (value: number): void => {
        muteBars.value = Math.max(1, Math.min(16, value)); // 1-16 bars
        saveSettings();
    };

    /**
     * Set auto-increment
     */
    const setAutoIncrement = (enabled: boolean): void => {
        autoIncrement.value = enabled;
        saveSettings();

        // Start or stop auto-increment based on playing state
        if (enabled && isPlaying.value) {
            startAutoIncrement();
        } else if (!enabled) {
            stopAutoIncrement();
        }
    };

    /**
     * Set auto-increment interval
     */
    const setAutoIncrementInterval = (seconds: number): void => {
        autoIncrementInterval.value = Math.max(5, Math.min(600, seconds)); // 5 seconds to 10 minutes
        saveSettings();
    };

    /**
     * Set auto-increment amount
     */
    const setAutoIncrementAmount = (amount: number): void => {
        autoIncrementAmount.value = Math.max(1, Math.min(50, amount)); // 1-50 BPM
        saveSettings();
    };

    /**
     * Set use target BPM
     */
    const setUseTargetBpm = (enabled: boolean): void => {
        useTargetBpm.value = enabled;
        saveSettings();
    };

    /**
     * Set target BPM
     */
    const setTargetBpm = (value: number): void => {
        targetBpm.value = Math.max(30, Math.min(300, value));
        saveSettings();
    };

    /**
     * Apply tempo preset
     */
    const applyTempoPreset = (preset: TempoPreset): void => {
        const presetValues: Record<TempoPreset, number> = {
            custom: bpm.value, // Keep current
            largo: 50,        // 40-60 BPM
            adagio: 71,       // 66-76 BPM
            andante: 92,      // 76-108 BPM
            moderato: 114,    // 108-120 BPM
            allegro: 144,     // 120-168 BPM
            presto: 184,      // 168-200 BPM
        };

        if (preset !== 'custom') {
            setBpm(presetValues[preset]);
        }
    };

    /**
     * Set speed trainer
     */
    const setSpeedTrainer = (enabled: boolean): void => {
        speedTrainer.value = enabled;
        saveSettings();
    };

    /**
     * Set speed trainer start BPM
     */
    const setSpeedTrainerStartBpm = (value: number): void => {
        speedTrainerStartBpm.value = Math.max(30, Math.min(300, value));
        saveSettings();
    };

    /**
     * Set speed trainer end BPM
     */
    const setSpeedTrainerEndBpm = (value: number): void => {
        speedTrainerEndBpm.value = Math.max(30, Math.min(300, value));
        saveSettings();
    };

    /**
     * Set speed trainer step size
     */
    const setSpeedTrainerStepSize = (value: number): void => {
        speedTrainerStepSize.value = Math.max(1, Math.min(50, value));
        saveSettings();
    };

    /**
     * Set speed trainer bars per tempo
     */
    const setSpeedTrainerBarsPerTempo = (value: number): void => {
        speedTrainerBarsPerTempo.value = Math.max(1, Math.min(32, value));
        saveSettings();
    };

    /**
     * Set speed trainer cycle
     */
    const setSpeedTrainerCycle = (enabled: boolean): void => {
        speedTrainerCycle.value = enabled;
        saveSettings();
    };

    // Load settings on initialization
    loadSettings();

    // Cleanup on unmount
    onUnmounted(() => {
        stop();
        audioEngine.dispose();
    });

    return {
        // State
        isPlaying,
        currentBeat,
        bpm,
        timeSignature,
        volume,
        soundType,
        accentFirstBeat,
        subdivision,
        countIn,
        isCountingIn,
        countInBeatsRemaining,
        muteMode,
        muteBars,
        playBars,
        barsPlayed,
        isMuted,
        autoIncrement,
        autoIncrementInterval,
        autoIncrementAmount,
        useTargetBpm,
        targetBpm,
        timeUntilIncrement,
        speedTrainer,
        speedTrainerStartBpm,
        speedTrainerEndBpm,
        speedTrainerStepSize,
        speedTrainerBarsPerTempo,
        speedTrainerCycle,
        speedTrainerCurrentBpm,
        speedTrainerBarsRemaining,

        // Computed
        beatsPerMeasure: displayBeatsPerMeasure,
        noteValue,

        // Methods
        start,
        stop,
        toggle,
        setBpm,
        setTimeSignature,
        setVolume,
        setSoundType,
        toggleAccent,
        tapTempo,
        incrementBpm,
        decrementBpm,
        setSubdivision,
        setCountIn,
        setMuteMode,
        setPlayBars,
        setMuteBars,
        setAutoIncrement,
        setAutoIncrementInterval,
        setAutoIncrementAmount,
        setUseTargetBpm,
        setTargetBpm,
        applyTempoPreset,
        setSpeedTrainer,
        setSpeedTrainerStartBpm,
        setSpeedTrainerEndBpm,
        setSpeedTrainerStepSize,
        setSpeedTrainerBarsPerTempo,
        setSpeedTrainerCycle,
    };
}

export default useMetronome;
