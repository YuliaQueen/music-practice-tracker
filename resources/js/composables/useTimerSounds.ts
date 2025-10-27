import {ref} from 'vue'

interface SoundSettings {
    enabled: boolean
    volume: number
    startSound: boolean
    pauseSound: boolean
    completeSound: boolean
    warningSound: boolean
}

const defaultSettings: SoundSettings = {
    enabled: true,
    volume: 0.7,
    startSound: true,
    pauseSound: true,
    completeSound: true,
    warningSound: true,
}

// Музыкальные ноты (частоты в Гц)
const NOTES = {
    C5: 523.25,
    E5: 659.25,
    G5: 783.99,
    A5: 880,
    C6: 1046.50,
    Cs6: 1108.73,
    E6: 1318.51,
    G6: 1567.98,
} as const

export function useTimerSounds() {
    const settings = ref<SoundSettings>({...defaultSettings})
    const audioContext = ref<AudioContext | null>(null)

    // Инициализация AudioContext
    const initAudioContext = async () => {
        if (!audioContext.value) {
            audioContext.value = new (window.AudioContext || (window as any).webkitAudioContext)()
        }
        // Resume context if it's suspended (required by browsers for autoplay policy)
        if (audioContext.value.state === 'suspended') {
            try {
                await audioContext.value.resume()
            } catch (e) {
                console.warn('AudioContext resume failed:', e)
            }
        }
    }

    // Генерация звука с помощью Web Audio API с плавными переходами
    const generateSound = (frequency: number, duration: number, type: OscillatorType = 'sine', attackTime = 0.01, releaseTime = 0.1) => {
        if (!settings.value.enabled) return

        initAudioContext()

        if (!audioContext.value) return

        const oscillator = audioContext.value.createOscillator()
        const gainNode = audioContext.value.createGain()

        oscillator.connect(gainNode)
        gainNode.connect(audioContext.value.destination)

        oscillator.frequency.setValueAtTime(frequency, audioContext.value.currentTime)
        oscillator.type = type

        // Плавное нарастание и затухание (ADSR envelope)
        const now = audioContext.value.currentTime
        const maxGain = settings.value.volume * 0.25

        gainNode.gain.setValueAtTime(0, now)
        gainNode.gain.linearRampToValueAtTime(maxGain, now + attackTime)
        gainNode.gain.linearRampToValueAtTime(maxGain * 0.7, now + duration - releaseTime)
        gainNode.gain.exponentialRampToValueAtTime(0.001, now + duration)

        oscillator.start(now)
        oscillator.stop(now + duration)
    }

    // Генерация аккорда (несколько частот одновременно)
    const generateChord = (frequencies: number[], duration: number, type: OscillatorType = 'sine') => {
        frequencies.forEach(freq => generateSound(freq, duration, type, 0.02, 0.15))
    }

    // Звук начала таймера - восходящий аккорд (C мажор)
    const playStartSound = () => {
        if (!settings.value.startSound) return
        // Мягкий C мажорный аккорд с арпеджио
        generateSound(NOTES.C5, 0.3, 'sine', 0.02, 0.2)
        setTimeout(() => generateSound(NOTES.E5, 0.3, 'sine', 0.02, 0.2), 50)
        setTimeout(() => generateSound(NOTES.G5, 0.35, 'sine', 0.02, 0.25), 100)
    }

    // Звук паузы - нисходящий мягкий звук
    const playPauseSound = () => {
        if (!settings.value.pauseSound) return
        // Мягкое нисходящее движение
        generateSound(NOTES.A5, 0.25, 'sine', 0.03, 0.18)
        setTimeout(() => generateSound(NOTES.E5, 0.3, 'sine', 0.03, 0.2), 80)
    }

    // Звук завершения блока - радостная мелодия
    const playCompleteSound = () => {
        if (!settings.value.completeSound) return
        // Позитивная восходящая мелодия (пентатоника)
        generateSound(NOTES.C5, 0.15, 'sine', 0.01, 0.1)
        setTimeout(() => generateSound(NOTES.E5, 0.15, 'sine', 0.01, 0.1), 100)
        setTimeout(() => generateSound(NOTES.G5, 0.15, 'sine', 0.01, 0.1), 200)
        setTimeout(() => generateSound(NOTES.C6, 0.25, 'sine', 0.02, 0.15), 300)
    }

    // Звук предупреждения - мягкое колокольчик-напоминание
    const playWarningSound = () => {
        if (!settings.value.warningSound) return
        // Мягкий колокольный звук
        generateSound(NOTES.A5, 0.2, 'sine', 0.01, 0.15)
        setTimeout(() => {
            generateSound(NOTES.A5, 0.2, 'sine', 0.01, 0.15)
            generateSound(NOTES.Cs6, 0.2, 'sine', 0.01, 0.15)
        }, 250)
    }

    // Звук окончания времени - приятный завершающий аккорд
    const playTimeUpSound = () => {
        if (!settings.value.completeSound) return
        // Финальный аккорд C мажор с октавой
        generateChord([NOTES.C5, NOTES.E5, NOTES.G5], 0.4, 'sine')
        setTimeout(() => {
            generateChord([NOTES.C6, NOTES.E6, NOTES.G6], 0.5, 'sine')
        }, 200)
    }

    // Звук переключения блока - легкий переход
    const playBlockSwitchSound = () => {
        if (!settings.value.startSound) return
        // Быстрый восходящий звук перехода
        generateSound(NOTES.G5, 0.12, 'sine', 0.01, 0.08)
        setTimeout(() => generateSound(NOTES.C6, 0.15, 'sine', 0.01, 0.1), 80)
    }

    // Обновление настроек
    const updateSettings = (newSettings: Partial<SoundSettings>) => {
        settings.value = {...settings.value, ...newSettings}
    }

    // Загрузка настроек из localStorage
    const loadSettings = () => {
        const saved = localStorage.getItem('timer-sound-settings')
        if (saved) {
            try {
                const parsed = JSON.parse(saved)
                settings.value = {...defaultSettings, ...parsed}
            } catch (e) {
                console.warn('Не удалось загрузить настройки звука:', e)
            }
        }
    }

    // Сохранение настроек в localStorage
    const saveSettings = () => {
        localStorage.setItem('timer-sound-settings', JSON.stringify(settings.value))
    }

    return {
        settings,
        playStartSound,
        playPauseSound,
        playCompleteSound,
        playWarningSound,
        playTimeUpSound,
        playBlockSwitchSound,
        updateSettings,
        loadSettings,
        saveSettings,
    }
}