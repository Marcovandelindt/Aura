<?php

namespace App\View\Composers;

use App\Services\ThemeService;
use Illuminate\View\View;

class ThemeComposer
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function compose(View $view): void
    {
        $view->with([
            'currentTheme' => $this->themeService->getCurrentTheme(),
            'themeClass' => $this->themeService->getThemeClass(),
            'availableThemes' => $this->themeService->getAvailableThemes(),
        ]);
    }
}