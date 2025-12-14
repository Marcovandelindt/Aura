<?php

namespace App\Services;

use App\Models\Setting;

class ThemeService
{
    const DEFAULT_THEME = 'light';

    const THEMES = [
        'light' => [
            'name' => 'Light',
            'icon' => 'fas fa-sun',
            'description' => 'Clean and bright interface',
        ],
        'dark' => [
            'name' => 'Dark',
            'icon' => 'fas fa-moon',
            'description' => 'Easy on the eyes',
        ],
        'christmas' => [
            'name' => 'Christmas',
            'icon' => 'fas fa-tree',
            'description' => 'Festive holiday spirit!',
        ],
    ];

    /**
     * Get current active theme
     */
    public function getCurrentTheme(): string
    {
        return Setting::get('app_theme', self::DEFAULT_THEME);
    }

    /**
     * Set the active theme
     */
    public function setTheme(string $theme): bool
    {
        if (! $this->isValidTheme($theme)) {
            return false;
        }

        Setting::set('app_theme', $theme);

        return true;
    }

    /**
     * Get all available themes
     */
    public function getAvailableThemes(): array
    {
        return self::THEMES;
    }

    /**
     * Check if theme is valid
     */
    public function isValidTheme(string $theme): bool
    {
        return array_key_exists($theme, self::THEMES);
    }

    /**
     * Get theme info
     */
    public function getThemeInfo(string $theme): ?array
    {
        return self::THEMES[$theme] ?? null;
    }

    /**
     * Get CSS class for current theme
     */
    public function getThemeClass(): string
    {
        return 'theme-'.$this->getCurrentTheme();
    }
}
