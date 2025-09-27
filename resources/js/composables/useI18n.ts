import { ref, computed } from 'vue';
import { useI18n as useVueI18n } from 'vue-i18n';

// Поддерживаемые языки
export type SupportedLocale = 'ru' | 'en';

// Состояние языка
const currentLocale = ref<SupportedLocale>('ru');

export function useI18n() {
  const { t, locale } = useVueI18n();

  // Получить текущий язык
  const getCurrentLocale = computed(() => currentLocale.value);

  // Проверить, является ли язык русским
  const isRussian = computed(() => currentLocale.value === 'ru');

  // Проверить, является ли язык английским
  const isEnglish = computed(() => currentLocale.value === 'en');

  // Установить язык
  const setLocale = (newLocale: SupportedLocale) => {
    currentLocale.value = newLocale;
    locale.value = newLocale;
    localStorage.setItem('locale', newLocale);
    
    // Обновляем атрибут lang в HTML
    document.documentElement.lang = newLocale;
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
      setLocale('ru');
    }
  };

  // Переключить язык
  const toggleLocale = () => {
    const newLocale = currentLocale.value === 'ru' ? 'en' : 'ru';
    setLocale(newLocale);
  };

  // Получить список доступных языков
  const availableLocales = computed(() => {
    try {
      return [
        { code: 'ru', name: t('languages.ru'), flag: '🇷🇺' },
        { code: 'en', name: t('languages.en'), flag: '🇺🇸' },
      ];
    } catch (error) {
      console.warn('Error getting available locales:', error);
      return [
        { code: 'ru', name: 'Русский', flag: '🇷🇺' },
        { code: 'en', name: 'English', flag: '🇺🇸' },
      ];
    }
  });

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