<?php

namespace App\Http\Controllers;

use App\Services\Spotify\SpotifyService;
use Illuminate\View\View;

class HomeController extends Controller 
{
    protected SpotifyService $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Index action
     * 
     * @return View
     */
    public function index(): View
    {
        $spotifyConnected = $this->spotifyService->isConnected();
        
        return view('home.index', compact('spotifyConnected'));
    }
}