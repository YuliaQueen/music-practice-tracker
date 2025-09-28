import { ref } from 'vue'

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

export function useTimerSounds() {
  const settings = ref<SoundSettings>({ ...defaultSettings })
  const audioContext = ref<AudioContext | null>(null)

  // Инициализация AudioContext
  const initAudioContext = () => {
    if (!audioContext.value) {
      audioContext.value = new (window.AudioContext || (window as any).webkitAudioContext)()
    }
  }

  // Генерация звука с помощью Web Audio API
  const generateSound = (frequency: number, duration: number, type: OscillatorType = 'sine') => {
    if (!settings.value.enabled) return

    initAudioContext()
    
    if (!audioContext.value) return

    const oscillator = audioContext.value.createOscillator()
    const gainNode = audioContext.value.createGain()

    oscillator.connect(gainNode)
    gainNode.connect(audioContext.value.destination)

    oscillator.frequency.setValueAtTime(frequency, audioContext.value.currentTime)
    oscillator.type = type

    // Настройка громкости
    gainNode.gain.setValueAtTime(0, audioContext.value.currentTime)
    gainNode.gain.linearRampToValueAtTime(settings.value.volume * 0.3, audioContext.value.currentTime + 0.01)
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.value.currentTime + duration)

    oscillator.start(audioContext.value.currentTime)
    oscillator.stop(audioContext.value.currentTime + duration)
  }

  // Звук начала таймера
  const playStartSound = () => {
    if (!settings.value.startSound) return
    generateSound(800, 0.2, 'sine')
  }

  // Звук паузы
  const playPauseSound = () => {
    if (!settings.value.pauseSound) return
    generateSound(600, 0.15, 'triangle')
  }

  // Звук завершения блока
  const playCompleteSound = () => {
    if (!settings.value.completeSound) return
    // Восходящая мелодия
    generateSound(523, 0.1, 'sine') // C5
    setTimeout(() => generateSound(659, 0.1, 'sine'), 100) // E5
    setTimeout(() => generateSound(784, 0.2, 'sine'), 200) // G5
  }

  // Звук предупреждения (за 30 секунд до окончания)
  const playWarningSound = () => {
    if (!settings.value.warningSound) return
    // Двойной звук предупреждения
    generateSound(1000, 0.1, 'square')
    setTimeout(() => generateSound(1000, 0.1, 'square'), 150)
  }

  // Звук окончания времени
  const playTimeUpSound = () => {
    if (!settings.value.completeSound) return
    // Серия звуков окончания времени
    generateSound(400, 0.2, 'square')
    setTimeout(() => generateSound(300, 0.2, 'square'), 200)
    setTimeout(() => generateSound(200, 0.3, 'square'), 400)
  }

  // Звук переключения блока
  const playBlockSwitchSound = () => {
    if (!settings.value.startSound) return
    generateSound(1000, 0.1, 'sine')
    setTimeout(() => generateSound(1200, 0.1, 'sine'), 100)
  }

  // Обновление настроек
  const updateSettings = (newSettings: Partial<SoundSettings>) => {
    settings.value = { ...settings.value, ...newSettings }
  }

  // Загрузка настроек из localStorage
  const loadSettings = () => {
    const saved = localStorage.getItem('timer-sound-settings')
    if (saved) {
      try {
        const parsed = JSON.parse(saved)
        settings.value = { ...defaultSettings, ...parsed }
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