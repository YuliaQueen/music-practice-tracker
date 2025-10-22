import { ref, computed } from 'vue';
// import { useI18n as useVueI18n } from 'vue-i18n'; // Отключено для стабильности

// Поддерживаемые языки
export type SupportedLocale = 'ru' | 'en';

// Состояние языка
const currentLocale = ref<SupportedLocale>('ru');

// Простые переводы (fallback)
const fallbackTranslations = {
  ru: {
    'languages.ru': 'Русский',
    'languages.en': 'English',
    'home.title': 'Music Practice Tracker - Ваш помощник в музыкальных занятиях',
    'home.appName': 'Music Practice Tracker',
    'home.hero.title': 'Отслеживайте свои музыкальные занятия',
    'home.hero.subtitle': 'Создавайте структурированные занятия, используйте таймеры для упражнений и отслеживайте свой прогресс в музыке',
    'home.hero.getStarted': 'Начать бесплатно',
    'home.hero.goToDashboard': 'Перейти к дашборду',
    'home.features.timer.title': 'Таймеры для упражнений',
    'home.features.timer.description': 'Установите время для каждого упражнения и следите за прогрессом с помощью встроенных таймеров',
    'home.features.tracking.title': 'Отслеживание прогресса',
    'home.features.tracking.description': 'Анализируйте статистику занятий, отслеживайте время практики и видите свой рост',
    'home.features.notes.title': 'Прикрепление нот',
    'home.features.notes.description': 'Добавляйте ноты, аудиофайлы и другие материалы к упражнениям для удобного доступа',
    'home.howItWorks.title': 'Как это работает',
    'home.howItWorks.step1.title': 'Создайте занятие',
    'home.howItWorks.step1.description': 'Создайте новое занятие и добавьте упражнения с таймерами',
    'home.howItWorks.step2.title': 'Добавьте упражнения',
    'home.howItWorks.step2.description': 'Настройте упражнения с временными интервалами и прикрепите ноты',
    'home.howItWorks.step3.title': 'Начните практику',
    'home.howItWorks.step3.description': 'Запустите занятие и следите за временем каждого упражнения',
    'home.howItWorks.step4.title': 'Отслеживайте прогресс',
    'home.howItWorks.step4.description': 'Анализируйте статистику и видите свой рост в музыке',
    'home.cta.title': 'Готовы начать?',
    'home.cta.description': 'Присоединяйтесь к музыкантам, которые уже используют Music Practice Tracker для улучшения своих навыков',
    'home.cta.button': 'Начать сейчас',
    'home.footer.text': '© 2025 Music Practice Tracker. Создано с любовью для музыкантов.',
    'dashboard.title': 'Дашборд',
    'dashboard.welcome': 'Добро пожаловать в Music Practice Tracker!',
    'dashboard.description': 'Это ваш личный помощник для планирования и отслеживания музыкальных занятий. Здесь вы можете создавать структурированные занятия, использовать таймеры для упражнений и отслеживать свой прогресс.',
    'dashboard.features': 'Возможности',
    'dashboard.feature1': 'Создание структурированных занятий',
    'dashboard.feature2': 'Таймеры для каждого упражнения',
    'dashboard.feature3': 'Отслеживание прогресса',
    'dashboard.feature4': 'Шаблоны занятий',
    'dashboard.howToStart': 'Как начать',
    'dashboard.step1': 'Создайте ваше первое занятие',
    'dashboard.step2': 'Добавьте упражнения с таймерами',
    'dashboard.step3': 'Начните занятие и следите за прогрессом',
    'dashboard.step4': 'Создавайте шаблоны для повторного использования',
    'dashboard.showAll': 'Показать все',
    'navigation.dashboard': 'Дашборд',
    'navigation.login': 'Войти',
    'navigation.register': 'Регистрация',
    'navigation.profile': 'Профиль',
    'navigation.logout': 'Выйти',
    'notes.title': 'Ноты',
    'notes.upload_new': 'Загрузить ноты',
    'notes.search': 'Поиск',
    'notes.search_placeholder': 'Поиск по названию или описанию...',
    'notes.file_type': 'Тип файла',
    'notes.all_types': 'Все типы',
    'notes.images': 'Изображения',
    'notes.audio': 'Аудио',
    'notes.exercise': 'Упражнение',
    'notes.all_exercises': 'Все упражнения',
    'notes.visibility': 'Видимость',
    'notes.all_notes': 'Все ноты',
    'notes.public': 'Публичные',
    'notes.private': 'Приватные',
    'notes.no_notes': 'Нот пока нет',
    'notes.no_notes_description': 'Загрузите свои первые ноты, чтобы начать работу с ними.',
    'notes.upload_first': 'Загрузить первые ноты',
    'notes.view': 'Просмотр',
    'notes.confirm_delete': 'Вы уверены, что хотите удалить эти ноты?',
    'notes.select_file': 'Выберите файл',
    'notes.drag_drop_file': 'Перетащите файл сюда',
    'notes.or_click_to_browse': 'или нажмите для выбора',
    'notes.browse_files': 'Выбрать файл',
    'notes.remove_file': 'Удалить файл',
    'notes.supported_formats': 'Поддерживаемые форматы',
    'notes.max_file_size': 'Максимальный размер файла',
    'notes.title_placeholder': 'Введите название нот...',
    'notes.description': 'Описание',
    'notes.description_placeholder': 'Добавьте описание или заметки...',
    'notes.associate_exercise': 'Связать с упражнением',
    'notes.no_exercise': 'Без упражнения',
    'notes.make_public': 'Сделать публичными',
    'notes.public_description': 'Публичные ноты будут доступны всем пользователям',
    'notes.uploading': 'Загрузка...',
    'notes.upload': 'Загрузить',
    'notes.file_too_large': 'Файл слишком большой (максимум 50MB)',
    'notes.unsupported_file_type': 'Неподдерживаемый тип файла',
    'notes.file_required': 'Необходимо выбрать файл',
    'notes.file_info': 'Информация о файле',
    'notes.filename': 'Имя файла',
    'notes.file_size': 'Размер файла',
    'notes.uploaded': 'Загружено',
    'notes.associated_exercise': 'Связанное упражнение',
    'notes.download': 'Скачать',
    'notes.edit_note': 'Редактировать ноты',
    'notes.file_actions': 'Действия с файлом',
    'notes.download_current_file': 'Скачать текущий файл',
    'notes.replace_file_note': 'Чтобы заменить файл, удалите текущие ноты и загрузите новые.',
    'notes.delete': 'Удалить',
    'notes.saving': 'Сохранение...',
    'notes.save_changes': 'Сохранить изменения',
    'notes.loading': 'Загрузка...',
    'notes.load_error': 'Ошибка загрузки файла',
    'notes.retry': 'Повторить',
    'notes.audio_not_supported': 'Ваш браузер не поддерживает аудио элемент.',
    'notes.musicxml_description': 'MusicXML файлы можно открыть в специализированных программах для работы с нотами.',
    'notes.download_to_view': 'Скачать для просмотра',
    'notes.preview_not_available': 'Предпросмотр недоступен для этого типа файла.',
    'notes.fullscreen': 'Полный экран',
    'notes.exit_fullscreen': 'Выйти из полноэкранного режима',
    'notes.zoom_in': 'Увеличить',
    'notes.zoom_out': 'Уменьшить',
    'common.back': 'Назад',
    'common.cancel': 'Отмена',
    'common.edit': 'Редактировать',
    'metronome.title': 'Метроном',
    'metronome.bpm': 'BPM',
    'metronome.timeSignature': 'Размер',
    'metronome.volume': 'Громкость',
    'metronome.soundType': 'Тип звука',
    'metronome.accentFirstBeat': 'Акцент на первую долю',
    'metronome.start': 'Старт',
    'metronome.stop': 'Стоп',
    'metronome.tap': 'Tap',
    'metronome.sounds.click': 'Клик',
    'metronome.sounds.beep': 'Бип',
    'metronome.sounds.wood': 'Дерево',
    'pagination.previous': 'Предыдущая',
    'pagination.next': 'Следующая',
    'pagination.showing': 'Показано',
    'pagination.to': 'до',
    'pagination.of': 'из',
    'pagination.results': 'результатов',
  },
  en: {
    'languages.ru': 'Русский',
    'languages.en': 'English',
    'home.title': 'Music Practice Tracker - Your Music Practice Assistant',
    'home.appName': 'Music Practice Tracker',
    'home.hero.title': 'Track Your Music Practice',
    'home.hero.subtitle': 'Create structured practice sessions, use timers for exercises, and track your musical progress',
    'home.hero.getStarted': 'Get Started Free',
    'home.hero.goToDashboard': 'Go to Dashboard',
    'home.features.timer.title': 'Exercise Timers',
    'home.features.timer.description': 'Set time for each exercise and track progress with built-in timers',
    'home.features.tracking.title': 'Progress Tracking',
    'home.features.tracking.description': 'Analyze practice statistics, track practice time, and see your growth',
    'home.features.notes.title': 'Attach Notes',
    'home.features.notes.description': 'Add sheet music, audio files, and other materials to exercises for easy access',
    'home.howItWorks.title': 'How It Works',
    'home.howItWorks.step1.title': 'Create Session',
    'home.howItWorks.step1.description': 'Create a new practice session and add exercises with timers',
    'home.howItWorks.step2.title': 'Add Exercises',
    'home.howItWorks.step2.description': 'Set up exercises with time intervals and attach sheet music',
    'home.howItWorks.step3.title': 'Start Practice',
    'home.howItWorks.step3.description': 'Launch the session and track time for each exercise',
    'home.howItWorks.step4.title': 'Track Progress',
    'home.howItWorks.step4.description': 'Analyze statistics and see your musical growth',
    'home.cta.title': 'Ready to Start?',
    'home.cta.description': 'Join musicians who already use Music Practice Tracker to improve their skills',
    'home.cta.button': 'Start Now',
    'home.footer.text': '© 2025 Music Practice Tracker. Made with love for musicians.',
    'dashboard.title': 'Dashboard',
    'dashboard.welcome': 'Welcome to Music Practice Tracker!',
    'dashboard.description': 'This is your personal assistant for planning and tracking music practice sessions. Here you can create structured sessions, use timers for exercises, and track your progress.',
    'dashboard.features': 'Features',
    'dashboard.feature1': 'Creating structured practice sessions',
    'dashboard.feature2': 'Timers for each exercise',
    'dashboard.feature3': 'Progress tracking',
    'dashboard.feature4': 'Session templates',
    'dashboard.howToStart': 'How to get started',
    'dashboard.step1': 'Create your first session',
    'dashboard.step2': 'Add exercises with timers',
    'dashboard.step3': 'Start the session and track progress',
    'dashboard.step4': 'Create templates for reuse',
    'dashboard.showAll': 'Show all',
    'navigation.dashboard': 'Dashboard',
    'navigation.login': 'Log In',
    'navigation.register': 'Register',
    'navigation.profile': 'Profile',
    'navigation.logout': 'Log Out',
    'notes.title': 'Notes',
    'notes.upload_new': 'Upload Notes',
    'notes.search': 'Search',
    'notes.search_placeholder': 'Search by title or description...',
    'notes.file_type': 'File Type',
    'notes.all_types': 'All Types',
    'notes.images': 'Images',
    'notes.audio': 'Audio',
    'notes.exercise': 'Exercise',
    'notes.all_exercises': 'All Exercises',
    'notes.visibility': 'Visibility',
    'notes.all_notes': 'All Notes',
    'notes.public': 'Public',
    'notes.private': 'Private',
    'notes.no_notes': 'No notes yet',
    'notes.no_notes_description': 'Upload your first notes to get started.',
    'notes.upload_first': 'Upload First Notes',
    'notes.view': 'View',
    'notes.confirm_delete': 'Are you sure you want to delete these notes?',
    'notes.select_file': 'Select File',
    'notes.drag_drop_file': 'Drag and drop file here',
    'notes.or_click_to_browse': 'or click to browse',
    'notes.browse_files': 'Browse Files',
    'notes.remove_file': 'Remove File',
    'notes.supported_formats': 'Supported formats',
    'notes.max_file_size': 'Maximum file size',
    'notes.title_placeholder': 'Enter notes title...',
    'notes.description': 'Description',
    'notes.description_placeholder': 'Add description or notes...',
    'notes.associate_exercise': 'Associate with Exercise',
    'notes.no_exercise': 'No Exercise',
    'notes.make_public': 'Make Public',
    'notes.public_description': 'Public notes will be accessible to all users',
    'notes.uploading': 'Uploading...',
    'notes.upload': 'Upload',
    'notes.file_too_large': 'File is too large (maximum 50MB)',
    'notes.unsupported_file_type': 'Unsupported file type',
    'notes.file_required': 'File is required',
    'notes.file_info': 'File Information',
    'notes.filename': 'Filename',
    'notes.file_size': 'File Size',
    'notes.uploaded': 'Uploaded',
    'notes.associated_exercise': 'Associated Exercise',
    'notes.download': 'Download',
    'notes.edit_note': 'Edit Notes',
    'notes.file_actions': 'File Actions',
    'notes.download_current_file': 'Download Current File',
    'notes.replace_file_note': 'To replace the file, delete current notes and upload new ones.',
    'notes.delete': 'Delete',
    'notes.saving': 'Saving...',
    'notes.save_changes': 'Save Changes',
    'notes.loading': 'Loading...',
    'notes.load_error': 'Error loading file',
    'notes.retry': 'Retry',
    'notes.audio_not_supported': 'Your browser does not support the audio element.',
    'notes.musicxml_description': 'MusicXML files can be opened in specialized music notation software.',
    'notes.download_to_view': 'Download to View',
    'notes.preview_not_available': 'Preview not available for this file type.',
    'notes.fullscreen': 'Fullscreen',
    'notes.exit_fullscreen': 'Exit Fullscreen',
    'notes.zoom_in': 'Zoom In',
    'notes.zoom_out': 'Zoom Out',
    'common.back': 'Back',
    'common.cancel': 'Cancel',
    'common.edit': 'Edit',
    'metronome.title': 'Metronome',
    'metronome.bpm': 'BPM',
    'metronome.timeSignature': 'Time Signature',
    'metronome.volume': 'Volume',
    'metronome.soundType': 'Sound Type',
    'metronome.accentFirstBeat': 'Accent first beat',
    'metronome.start': 'Start',
    'metronome.stop': 'Stop',
    'metronome.tap': 'Tap',
    'metronome.sounds.click': 'Click',
    'metronome.sounds.beep': 'Beep',
    'metronome.sounds.wood': 'Wood',
    'pagination.previous': 'Previous',
    'pagination.next': 'Next',
    'pagination.showing': 'Showing',
    'pagination.to': 'to',
    'pagination.of': 'of',
    'pagination.results': 'results',
  },
};

export function useSimpleI18n() {
  // Отключаем vue-i18n для стабильности, используем только простые переводы

  // Получить текущий язык
  const getCurrentLocale = computed(() => currentLocale.value);

  // Проверить, является ли язык русским
  const isRussian = computed(() => currentLocale.value === 'ru');

  // Проверить, является ли язык английским
  const isEnglish = computed(() => currentLocale.value === 'en');

  // Установить язык
  const setLocale = (newLocale: SupportedLocale) => {
    currentLocale.value = newLocale;
    try {
      localStorage.setItem('locale', newLocale);
      document.documentElement.lang = newLocale;
    } catch (error) {
      console.warn('Error saving locale:', error);
    }
  };

  // Загрузить сохраненный язык
  const loadLocale = () => {
    try {
      const savedLocale = localStorage.getItem('locale') as SupportedLocale;
      if (savedLocale && (savedLocale === 'ru' || savedLocale === 'en')) {
        setLocale(savedLocale);
      } else {
        // Определяем язык браузера
        const browserLocale = navigator.language.split('-')[0];
        if (browserLocale === 'ru') {
          setLocale('ru');
        } else {
          setLocale('en');
        }
      }
    } catch (error) {
      console.warn('Error loading locale:', error);
      // Устанавливаем только локальное состояние
      currentLocale.value = 'ru';
      localStorage.setItem('locale', 'ru');
      document.documentElement.lang = 'ru';
    }
  };

  // Переключить язык
  const toggleLocale = () => {
    const newLocale = currentLocale.value === 'ru' ? 'en' : 'ru';
    setLocale(newLocale);
  };

  // Получить список доступных языков
  const availableLocales = computed(() => [
    { code: 'ru', name: 'Русский', flag: '🇷🇺' },
    { code: 'en', name: 'English', flag: '🇺🇸' },
  ]);

  // Функция перевода (только простые переводы)
  const t = (key: string) => {
    try {
      const translations = fallbackTranslations[currentLocale.value] as Record<string, string>;
      return translations[key] || key;
    } catch (error) {
      console.warn('Error using translations:', error);
      return key;
    }
  };

  return {
    // Состояние
    currentLocale: getCurrentLocale,
    isRussian,
    isEnglish,
    availableLocales,
    
    // Методы
    setLocale,
    loadLocale,
    toggleLocale,
    
    // Функция перевода
    t,
  };
}