import { ref, computed, onMounted } from 'vue';

type Theme = 'light' | 'dark';

const theme = ref<Theme>('light');

export function useTheme() {
    const isDark = computed(() => theme.value === 'dark');
    
    const toggleTheme = () => {
        theme.value = theme.value === 'light' ? 'dark' : 'light';
        updateDocumentClass();
        saveTheme();
    };
    
    const setTheme = (newTheme: Theme) => {
        theme.value = newTheme;
        updateDocumentClass();
        saveTheme();
    };
    
    const updateDocumentClass = () => {
        const html = document.documentElement;
        if (theme.value === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    };
    
    const saveTheme = () => {
        localStorage.setItem('theme', theme.value);
    };
    
    const loadTheme = () => {
        const savedTheme = localStorage.getItem('theme') as Theme;
        if (savedTheme && (savedTheme === 'light' || savedTheme === 'dark')) {
            theme.value = savedTheme;
        } else {
            // Проверяем системные настройки
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme.value = prefersDark ? 'dark' : 'light';
        }
        updateDocumentClass();
    };
    
    onMounted(() => {
        loadTheme();
    });
    
    return {
        theme: computed(() => theme.value),
        isDark,
        toggleTheme,
        setTheme,
    };
}