import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_STORAGE_KEY = 'themePreference';

function getStoredTheme() {
    try {
        return window.localStorage.getItem(THEME_STORAGE_KEY);
    } catch (error) {
        return null;
    }
}

function storeTheme(theme) {
    try {
        window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (error) {
        // ignore storage limitations
    }
}

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function getActiveTheme() {
    return document.documentElement.getAttribute('data-theme') || 'light';
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.colorScheme = theme;
}

function updateThemeToggleLabels() {
    const currentTheme = getActiveTheme();
    const nextThemeLabel = currentTheme === 'dark' ? 'Light mode' : 'Dark mode';

    document.querySelectorAll('[data-theme-toggle-label]').forEach((el) => {
        el.textContent = nextThemeLabel;
    });
}

function bindThemeToggleButtons() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (button.dataset.themeToggleBound === 'true') {
            return;
        }

        button.dataset.themeToggleBound = 'true';
        button.addEventListener('click', () => {
            window.toggleThemeMode();
        });
    });
}

function initializeTheme() {
    const storedTheme = getStoredTheme();
    const initialTheme = storedTheme || getSystemTheme();

    applyTheme(initialTheme);
    updateThemeToggleLabels();
    bindThemeToggleButtons();
}


window.setThemeMode = function setThemeMode(theme) {
    const normalized = theme === 'dark' ? 'dark' : 'light';

    applyTheme(normalized);
    storeTheme(normalized);
    updateThemeToggleLabels();
};

window.toggleThemeMode = function toggleThemeMode() {
    const nextTheme = getActiveTheme() === 'dark' ? 'light' : 'dark';

    applyTheme(nextTheme);
    storeTheme(nextTheme);
    updateThemeToggleLabels();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTheme);
} else {
    initializeTheme();
}

Alpine.start();
