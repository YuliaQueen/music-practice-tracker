import { ref, computed, onUnmounted, Ref } from 'vue';
import MetronomeAudioEngine from '@/utils/metronomeAudioEngine';

export type TimeSignature = '2/4' | '3/4' | '4/4' | '5/4' | '6/8' | '7/8' | '9/8' | '12/8';
export type SoundType = 'click' | 'beep' | 'wood';

interface MetronomeSettings {
    bpm: number;
    timeSignature: TimeSignature;
    volume: number;
    soundType: SoundType;
    accentFirstBeat: boolean;
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

    // Timing
    let timerID: number | null = null;

    // Load settings from localStorage
    const loadSettings = (): void => {
        const savedSettings = localStorage.getItem(SETTINGS_KEY);
        if (savedSettings) {
            try {
                const settings: MetronomeSettings = JSON.parse(savedSettings);
                bpm.value = settings.bpm || 120;
                timeSignature.value = settings.timeSignature || '4/4';
                volume.value = settings.volume !== undefined ? settings.volume : 0.5;
                soundType.value = settings.soundType || 'click';
                accentFirstBeat.value = settings.accentFirstBeat !== undefined ? settings.accentFirstBeat : true;
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

    /**
     * Schedule note to be played
     */
    const scheduleNote = (beatNumber: number, time: number): void => {
        // Determine if this beat should be accented
        const isAccent = accentFirstBeat.value && beatNumber === 0;

        // Schedule the sound
        playSound(time, isAccent);

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
            // Calculate which beat we're on
            let beatInMeasure = currentBeat.value % displayBeatsPerMeasure.value;

            // Schedule this note
            scheduleNote(beatInMeasure, nextNoteTime);

            // Advance to next beat
            nextNoteTime = nextNoteTime + secondsPerBeat.value;
            audioEngine.setNextNoteTime(nextNoteTime);

            // Increment beat counter
            currentBeat.value = (currentBeat.value + 1) % displayBeatsPerMeasure.value;

            iterationCount++;
        }

        // Schedule next scheduler call
        if (isPlaying.value) {
            timerID = window.setTimeout(scheduler, audioEngine.getLookahead());
        }
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
        audioEngine.setNextNoteTime(audioEngine.getCurrentTime());

        // Start scheduler
        isPlaying.value = true;
        scheduler();
    };

    /**
     * Stop the metronome
     */
    const stop = (): void => {
        if (!isPlaying.value) return;

        isPlaying.value = false;
        currentBeat.value = 0;

        if (timerID !== null) {
            clearTimeout(timerID);
            timerID = null;
        }
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
    };
}

export default useMetronome;
