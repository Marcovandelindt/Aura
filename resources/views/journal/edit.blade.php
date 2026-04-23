@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <h1>Entry bewerken</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li><a href="{{ route('journal.index') }}">Journal</a></li>
                <li><a href="{{ route('journal.show', $journalEntry) }}">{{ $journalEntry->title }}</a></li>
                <li>Bewerken</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('journal.update', $journalEntry) }}" id="journal-form">
        @csrf
        @method('PUT')
        @include('journal._form', ['entry' => $journalEntry, 'moods' => $moods, 'tags' => $tags])
    </form>
</div>
@endsection

@push('scripts')
    @vite('resources/js/journal-editor.js')
@endpush
