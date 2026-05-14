@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <h1>{{ $person->name }}</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li>{{ $person->name }}</li>
            </ul>
        </div>
    </div>

    {{-- Profile header --}}
    <div class="card">
        <div class="card-body">
            <div style="display: flex; gap: 2rem; align-items: flex-start;">
                @if($person->profile_url)
                    <img src="{{ $person->profile_url }}" alt="{{ $person->name }}" style="width: 150px; border-radius: 0.5rem; flex-shrink: 0;">
                @else
                    <div style="width: 150px; height: 225px; background: #374151; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-user" style="color: #6b7280; font-size: 3rem;"></i>
                    </div>
                @endif

                <div style="flex: 1;">
                    <h2 style="margin-bottom: 1.5rem;">{{ $person->name }}</h2>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: #f3f4f6;">{{ $watchedMovies->count() }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Films</div>
                        </div>
                        <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: #f3f4f6;">{{ $totalMovieWatches }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Film watches</div>
                        </div>
                        <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: #f3f4f6;">{{ $person->tvSeries->count() }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">TV series</div>
                        </div>
                        <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: #f3f4f6;">{{ $totalEpisodesWatched }}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Afleveringen</div>
                        </div>
                        <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 1.75rem; font-weight: 700; color: #f3f4f6;">{{ $totalHours }}u</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Gekeken tijd</div>
                        </div>
                        @if($firstSeen)
                            <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #f3f4f6;">{{ $firstSeen->format('d M Y') }}</div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Eerste keer gezien</div>
                            </div>
                        @endif
                        @if($lastSeen)
                            <div style="background: #1f2937; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #f3f4f6;">{{ $lastSeen->format('d M Y') }}</div>
                                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Laatste keer gezien</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Movies --}}
    @if($watchedMovies->isNotEmpty())
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>Films ({{ $watchedMovies->count() }})</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem;">
                    @foreach($watchedMovies as $movie)
                        <a href="{{ route('movies.show', $movie) }}" style="text-decoration: none;">
                            <div style="background: #1f2937; border-radius: 0.5rem; overflow: hidden; text-align: center;">
                                @if($movie->poster_url)
                                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" style="width: 100%; aspect-ratio: 2/3; object-fit: cover;">
                                @else
                                    <div style="width: 100%; aspect-ratio: 2/3; background: #374151; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-film" style="color: #6b7280; font-size: 2rem;"></i>
                                    </div>
                                @endif
                                <div style="padding: 0.5rem;">
                                    <div style="font-weight: 500; font-size: 0.8125rem; line-height: 1.3; color: #f3f4f6;">{{ $movie->title }}</div>
                                    @php $role = $movie->pivot->character ?? $movie->pivot->job; @endphp
                                    @if($role)
                                        <div style="color: #6b7280; font-size: 0.75rem; margin-top: 0.25rem;">{{ $role }}</div>
                                    @endif
                                    @if($movie->watch_count > 1)
                                        <div style="color: #4b5563; font-size: 0.75rem; margin-top: 0.125rem;">{{ $movie->watch_count }}x gezien</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- TV Series --}}
    @if($person->tvSeries->isNotEmpty())
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>TV Series ({{ $person->tvSeries->count() }})</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($person->tvSeries as $series)
                        <a href="{{ route('tv.show', $series) }}" style="text-decoration: none;">
                            <div style="display: flex; gap: 1rem; align-items: center; background: #1f2937; padding: 0.75rem; border-radius: 0.5rem;">
                                @if($series->poster_url)
                                    <img src="{{ $series->poster_url }}" alt="{{ $series->name }}" style="width: 50px; height: 75px; object-fit: cover; border-radius: 0.25rem; flex-shrink: 0;">
                                @else
                                    <div style="width: 50px; height: 75px; background: #374151; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-tv" style="color: #6b7280;"></i>
                                    </div>
                                @endif
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 500; color: #f3f4f6;">{{ $series->name_en ?? $series->name }}</div>
                                    @php $character = $series->pivot->character ?? $series->pivot->job; @endphp
                                    @if($character)
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">{{ $character }}</div>
                                    @endif
                                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
                                        @if($series->pivot->episode_count)
                                            <span style="color: #9ca3af; font-size: 0.8125rem;">
                                                <i class="fas fa-film" style="margin-right: 0.25rem;"></i>{{ $series->pivot->episode_count }} afl. in serie
                                            </span>
                                        @endif
                                        @if($series->episodes_watched > 0)
                                            <span style="color: #10b981; font-size: 0.8125rem;">
                                                <i class="fas fa-eye" style="margin-right: 0.25rem;"></i>{{ $series->episodes_watched }} afl. gezien
                                            </span>
                                        @endif
                                        @if($series->completion_percentage > 0)
                                            <span style="color: #9ca3af; font-size: 0.8125rem;">
                                                {{ number_format($series->completion_percentage) }}%
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($series->last_watched_at)
                                    <div style="color: #6b7280; font-size: 0.75rem; text-align: right; flex-shrink: 0;">
                                        {{ $series->last_watched_at->format('d M Y') }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
