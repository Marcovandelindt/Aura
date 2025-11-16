<?php

namespace App\Http\Middleware;

use App\Services\Spotify\SpotifyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSpotifyIsConnected
{
    protected SpotifyService $spotifyService;
    
    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->spotifyService->isConnected()) {
            return redirect()->route('spotify.auth')
                ->with('warning', 'Please connect your Spotify account first.');
        }
        
        return $next($request);
    }
}
