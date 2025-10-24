/**
 * Metronome Audio Engine
 * Uses Web Audio API to generate precise metronome clicks
 */

export interface MetronomeSound {
    frequency: number;
    duration: number;
    volume: number;
}

export class MetronomeAudioEngine {
    private audioContext: AudioContext | null = null;
    private nextNoteTime: number = 0;
    private lookahead: number = 25.0; // How frequently to call scheduling function (in milliseconds)
    private scheduleAheadTime: number = 0.1; // How far ahead to schedule audio (sec)

    constructor() {
        // Audio Context will be initialized on first interaction (due to browser autoplay policies)
    }

    /**
     * Initialize Audio Context
     */
    public init(): void {
        if (!this.audioContext) {
            this.audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
        }
    }

    /**
     * Resume audio context if suspended (required for iOS Safari)
     */
    public async resume(): Promise<void> {
        if (this.audioContext && this.audioContext.state === 'suspended') {
            await this.audioContext.resume();
        }
    }

    /**
     * Get current audio context time
     */
    public getCurrentTime(): number {
        return this.audioContext ? this.audioContext.currentTime : 0;
    }

    /**
     * Set next note time
     */
    public setNextNoteTime(time: number): void {
        this.nextNoteTime = time;
    }

    /**
     * Get next note time
     */
    public getNextNoteTime(): number {
        return this.nextNoteTime;
    }

    /**
     * Get schedule ahead time
     */
    public getScheduleAheadTime(): number {
        return this.scheduleAheadTime;
    }

    /**
     * Get lookahead interval
     */
    public getLookahead(): number {
        return this.lookahead;
    }

    /**
     * Play a click sound
     * @param time - When to play the sound
     * @param isAccent - Whether this is an accented beat
     * @param volume - Volume level (0-1)
     */
    public playClick(time: number, isAccent: boolean = false, volume: number = 0.5): void {
        if (!this.audioContext) {
            this.init();
        }

        if (!this.audioContext) {
            console.error('Audio context not available');
            return;
        }

        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();

        // Much more prominent difference for accent beats
        oscillator.frequency.value = isAccent ? 1200 : 800;

        // Connect nodes
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);

        // Volume envelope for click sound with stronger accent
        // Multiply by 1.5 for better audibility
        const clickVolume = isAccent ? volume * 3.0 : volume * 1.5;
        gainNode.gain.value = clickVolume;
        gainNode.gain.exponentialRampToValueAtTime(0.01, time + 0.03);

        // Play the sound
        oscillator.start(time);
        oscillator.stop(time + 0.03);
    }

    /**
     * Play a custom sound with specified parameters
     */
    public playCustomSound(
        time: number,
        frequency: number,
        duration: number,
        volume: number
    ): void {
        if (!this.audioContext) {
            this.init();
        }

        if (!this.audioContext) {
            console.error('Audio context not available');
            return;
        }

        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();

        oscillator.frequency.value = frequency;
        oscillator.type = 'sine';

        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);

        gainNode.gain.value = volume;
        gainNode.gain.exponentialRampToValueAtTime(0.01, time + duration);

        oscillator.start(time);
        oscillator.stop(time + duration);
    }

    /**
     * Play wood block sound (using noise)
     */
    public playWoodBlock(time: number, isAccent: boolean = false, volume: number = 0.5): void {
        if (!this.audioContext) {
            this.init();
        }

        if (!this.audioContext) {
            console.error('Audio context not available');
            return;
        }

        const context = this.audioContext;
        const bufferSize = context.sampleRate * 0.05; // 50ms of noise
        const buffer = context.createBuffer(1, bufferSize, context.sampleRate);
        const output = buffer.getChannelData(0);

        // Generate noise
        for (let i = 0; i < bufferSize; i++) {
            output[i] = Math.random() * 2 - 1;
        }

        const noise = context.createBufferSource();
        noise.buffer = buffer;

        const filter = context.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.value = isAccent ? 800 : 500;
        filter.Q.value = 10;

        const gainNode = context.createGain();
        // Much louder base volume and stronger accent for wood block
        const clickVolume = isAccent ? volume * 3.0 : volume * 1.5;
        gainNode.gain.value = clickVolume;
        gainNode.gain.exponentialRampToValueAtTime(0.01, time + 0.05);

        noise.connect(filter);
        filter.connect(gainNode);
        gainNode.connect(context.destination);

        noise.start(time);
        noise.stop(time + 0.05);
    }

    /**
     * Play beep sound (pure tone)
     */
    public playBeep(time: number, isAccent: boolean = false, volume: number = 0.5): void {
        if (!this.audioContext) {
            this.init();
        }

        if (!this.audioContext) {
            console.error('Audio context not available');
            return;
        }

        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();

        // Higher pitch and stronger accent for beep
        oscillator.frequency.value = isAccent ? 1400 : 900;
        oscillator.type = 'sine';

        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);

        // Multiply by 1.5 for better audibility
        const clickVolume = isAccent ? volume * 3.0 : volume * 1.5;
        gainNode.gain.value = clickVolume;
        gainNode.gain.exponentialRampToValueAtTime(0.01, time + 0.1);

        oscillator.start(time);
        oscillator.stop(time + 0.1);
    }

    /**
     * Clean up resources
     */
    public dispose(): void {
        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }
    }
}

export default MetronomeAudioEngine;
