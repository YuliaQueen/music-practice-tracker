import { ref, computed, onUnmounted } from 'vue'

export interface AudioRecordingState {
    isRecording: boolean
    isPaused: boolean
    duration: number
    audioBlob: Blob | null
    audioUrl: string | null
    error: string | null
}

export function useAudioRecorder() {
    const mediaRecorder = ref<MediaRecorder | null>(null)
    const audioChunks = ref<Blob[]>([])
    const stream = ref<MediaStream | null>(null)

    const isRecording = ref(false)
    const isPaused = ref(false)
    const duration = ref(0)
    const audioBlob = ref<Blob | null>(null)
    const audioUrl = ref<string | null>(null)
    const error = ref<string | null>(null)
    const isSupported = ref(true)

    let startTime = 0
    let pauseTime = 0
    let durationInterval: number | null = null

    // Проверка поддержки MediaRecorder
    if (!navigator.mediaDevices || !window.MediaRecorder) {
        isSupported.value = false
        error.value = 'Ваш браузер не поддерживает запись аудио'
    }

    const startDurationTracking = () => {
        startTime = Date.now()
        durationInterval = window.setInterval(() => {
            if (isRecording.value && !isPaused.value) {
                duration.value = Math.floor((Date.now() - startTime) / 1000)
            }
        }, 100)
    }

    const stopDurationTracking = () => {
        if (durationInterval) {
            clearInterval(durationInterval)
            durationInterval = null
        }
    }

    const startRecording = async () => {
        try {
            error.value = null

            // Запрос доступа к микрофону
            stream.value = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100,
                }
            })

            // Определяем поддерживаемый формат
            const mimeType = MediaRecorder.isTypeSupported('audio/webm; codecs=opus')
                ? 'audio/webm; codecs=opus'
                : MediaRecorder.isTypeSupported('audio/webm')
                ? 'audio/webm'
                : MediaRecorder.isTypeSupported('audio/mp4')
                ? 'audio/mp4'
                : ''

            if (!mimeType) {
                throw new Error('Нет поддерживаемого формата аудио')
            }

            mediaRecorder.value = new MediaRecorder(stream.value, { mimeType })
            audioChunks.value = []

            mediaRecorder.value.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunks.value.push(event.data)
                }
            }

            mediaRecorder.value.onstop = () => {
                const blob = new Blob(audioChunks.value, { type: mimeType })
                audioBlob.value = blob
                audioUrl.value = URL.createObjectURL(blob)
                stopDurationTracking()
            }

            mediaRecorder.value.onerror = (event) => {
                console.error('MediaRecorder error:', event)
                error.value = 'Ошибка при записи аудио'
                stopRecording()
            }

            mediaRecorder.value.start(100) // Собираем данные каждые 100мс
            isRecording.value = true
            isPaused.value = false
            startDurationTracking()

        } catch (err) {
            console.error('Error starting recording:', err)
            if (err instanceof Error) {
                if (err.name === 'NotAllowedError') {
                    error.value = 'Доступ к микрофону запрещен. Разрешите доступ в настройках браузера.'
                } else if (err.name === 'NotFoundError') {
                    error.value = 'Микрофон не найден. Подключите микрофон и попробуйте снова.'
                } else {
                    error.value = 'Не удалось начать запись: ' + err.message
                }
            } else {
                error.value = 'Не удалось начать запись'
            }
            isRecording.value = false
        }
    }

    const pauseRecording = () => {
        if (mediaRecorder.value && mediaRecorder.value.state === 'recording') {
            mediaRecorder.value.pause()
            isPaused.value = true
            pauseTime = Date.now()
        }
    }

    const resumeRecording = () => {
        if (mediaRecorder.value && mediaRecorder.value.state === 'paused') {
            mediaRecorder.value.resume()
            isPaused.value = false
            // Корректируем время начала с учетом паузы
            const pauseDuration = Date.now() - pauseTime
            startTime += pauseDuration
        }
    }

    const stopRecording = () => {
        if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
            mediaRecorder.value.stop()
            isRecording.value = false
            isPaused.value = false
        }

        // Останавливаем все треки медиа стрима
        if (stream.value) {
            stream.value.getTracks().forEach(track => track.stop())
            stream.value = null
        }
    }

    const resetRecording = () => {
        stopRecording()
        audioChunks.value = []
        audioBlob.value = null
        if (audioUrl.value) {
            URL.revokeObjectURL(audioUrl.value)
            audioUrl.value = null
        }
        duration.value = 0
        error.value = null
    }

    const getRecordingBlob = () => {
        return audioBlob.value
    }

    const getRecordingUrl = () => {
        return audioUrl.value
    }

    const getMimeType = () => {
        return mediaRecorder.value?.mimeType || 'audio/webm'
    }

    // Форматирование времени для отображения
    const formattedDuration = computed(() => {
        const mins = Math.floor(duration.value / 60)
        const secs = duration.value % 60
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
    })

    // Очистка при размонтировании компонента
    onUnmounted(() => {
        stopRecording()
        if (audioUrl.value) {
            URL.revokeObjectURL(audioUrl.value)
        }
    })

    return {
        // State
        isRecording,
        isPaused,
        duration,
        formattedDuration,
        audioBlob,
        audioUrl,
        error,
        isSupported,

        // Methods
        startRecording,
        pauseRecording,
        resumeRecording,
        stopRecording,
        resetRecording,
        getRecordingBlob,
        getRecordingUrl,
        getMimeType,
    }
}
