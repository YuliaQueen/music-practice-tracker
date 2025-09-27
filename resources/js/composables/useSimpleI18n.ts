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
    'navigation.profile': 'Профиль',
    'navigation.logout': 'Выйти',
  },
  en: {
    'languages.ru': 'Русский', 
    'languages.en': 'English',
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
    'navigation.profile': 'Profile',
    'navigation.logout': 'Log Out',
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