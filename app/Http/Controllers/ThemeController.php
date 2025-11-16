<?php

namespace App\Http\Controllers;

use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Switch to a specific theme
     */
    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $theme = $request->input('theme');

        if (!$this->themeService->isValidTheme($theme)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid theme selected'
                ], 400);
            }

            return redirect()->back()->with('error', 'Invalid theme selected');
        }

        $this->themeService->setTheme($theme);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'theme' => $theme,
                'themeClass' => $this->themeService->getThemeClass(),
                'message' => 'Theme switched successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Theme switched to ' . $this->themeService->getThemeInfo($theme)['name']);
    }

    /**
     * Get current theme info
     */
    public function current(): JsonResponse
    {
        $currentTheme = $this->themeService->getCurrentTheme();
        
        return response()->json([
            'currentTheme' => $currentTheme,
            'themeClass' => $this->themeService->getThemeClass(),
            'availableThemes' => $this->themeService->getAvailableThemes()
        ]);
    }
}
