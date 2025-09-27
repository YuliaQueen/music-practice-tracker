<template>
    <div class="relative">
        <button
            @click="showDropdown = !showDropdown"
            class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:text-gray-700 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200 dark:focus:bg-gray-800 dark:focus:text-gray-200"
            :title="t('theme.switchToLight')"
        >
            <!-- Иконка языка -->
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
                />
            </svg>
        </button>

        <!-- Выпадающее меню -->
        <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-600 dark:ring-opacity-50 z-50"
            @click.stop
        >
            <div class="py-1">
                <button
                    v-for="locale in availableLocales"
                    :key="locale.code"
                    @click="selectLanguage(locale.code)"
                    :class="[
                        'w-full text-left px-4 py-2 text-sm transition-colors duration-150',
                        currentLocale === locale.code
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                    ]"
                >
                    <div class="flex items-center space-x-3">
                        <span class="text-lg">{{ locale.flag }}</span>
                        <span>{{ locale.name }}</span>
                        <svg
                            v-if="currentLocale === locale.code"
                            class="ml-auto h-4 w-4 text-indigo-600 dark:text-indigo-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- Оверлей для закрытия меню -->
        <div
            v-if="showDropdown"
            @click="showDropdown = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useSimpleI18n } from '@/composables/useSimpleI18n';

const showDropdown = ref(false);

let currentLocale, availableLocales, setLocale, t;

try {
    const i18n = useSimpleI18n();
    currentLocale = i18n.currentLocale;
    availableLocales = i18n.availableLocales;
    setLocale = i18n.setLocale;
    t = i18n.t;
} catch (error) {
    console.warn('Error initializing LanguageSwitcher:', error);
    // Fallback values
    currentLocale = ref('ru');
    availableLocales = ref([
        { code: 'ru', name: 'Русский', flag: '🇷🇺' },
        { code: 'en', name: 'English', flag: '🇺🇸' },
    ]);
    setLocale = () => {};
    t = (key: string) => key;
}

const selectLanguage = (localeCode: string) => {
    try {
        setLocale(localeCode as 'ru' | 'en');
        showDropdown.value = false;
    } catch (error) {
        console.warn('Error selecting language:', error);
    }
};
</script>