import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const savedTheme = localStorage.getItem('crm_theme') || 'dark';
    const isDark = ref(savedTheme === 'dark');

    const applyTheme = (dark) => {
        if (dark) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('crm_theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('crm_theme', 'light');
        }
        isDark.value = dark;
    };

    const toggleTheme = () => {
        applyTheme(!isDark.value);
    };

    const initTheme = () => {
        applyTheme(isDark.value);
    };

    return {
        isDark,
        toggleTheme,
        applyTheme,
        initTheme,
    };
});
