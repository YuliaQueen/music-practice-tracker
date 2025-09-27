import { createI18n } from 'vue-i18n';
import ru from './locales/ru';
import en from './locales/en';

// Определяем язык по умолчанию
const defaultLocale = 'ru';

// Создаем экземпляр i18n
const i18n = createI18n({
  legacy: false, // Используем Composition API
  locale: defaultLocale,
  fallbackLocale: 'en',
  messages: {
    ru,
    en,
  },
  // Настройки для форматирования
  numberFormats: {
    ru: {
      currency: {
        style: 'currency',
        currency: 'RUB',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      },
    },
    en: {
      currency: {
        style: 'currency',
        currency: 'USD',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      },
    },
  },
  // Настройки для дат
  datetimeFormats: {
    ru: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: 'numeric',
        minute: 'numeric',
      },
      time: {
        hour: 'numeric',
        minute: 'numeric',
      },
    },
    en: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: 'numeric',
        minute: 'numeric',
      },
      time: {
        hour: 'numeric',
        minute: 'numeric',
      },
    },
  },
});

export default i18n;