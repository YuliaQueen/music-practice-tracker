/**
 * Общие TypeScript интерфейсы для моделей приложения
 * Centralized type definitions to avoid duplication
 */

/**
 * Аудио запись исполнения
 */
export interface AudioRecording {
    id: number
    title: string | null
    notes: string | null
    file_name: string
    audio_url: string
    formatted_duration: string | null
    formatted_file_size: string
    quality_rating: number | null
    recorded_at: string
}

/**
 * Блок сессии (упражнение в сессии)
 */
export interface SessionBlock {
    id: number
    practice_session_id: number
    title: string
    description: string
    type: string
    planned_duration: number
    actual_duration: number | null
    status: string
    sort_order: number
    started_at: string | null
    completed_at: string | null
    audioRecordings?: AudioRecording[]
}

/**
 * Сессия практики
 */
export interface Session {
    id: number
    user_id: number
    practice_template_id: number | null
    title: string
    description: string | null
    planned_duration: number
    actual_duration: number | null
    status: string
    auto_advance: boolean
    started_at: string | null
    completed_at: string | null
    created_at: string
    updated_at: string
    blocks: SessionBlock[]
    template?: any
    // Pomodoro поля
    session_mode: 'standard' | 'pomodoro'
    pomodoro_enabled: boolean
    pomodoro_work_duration: number
    pomodoro_short_break: number
    pomodoro_long_break: number
    pomodoro_cycles_before_long_break: number
    pomodoro_completed_cycles: number
    pomodoro_total_cycles: number
}
