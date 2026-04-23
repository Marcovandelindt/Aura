@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <h1>Nieuwe entry</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li><a href="{{ route('journal.index') }}">Journal</a></li>
                <li>Nieuwe entry</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('journal.store') }}" id="journal-form">
        @csrf
        @include('journal._form', ['entry' => null, 'moods' => $moods, 'tags' => $tags])
    </form>
</div>
@endsection

@push('scripts')
    @vite('resources/js/journal-editor.js')
@endpush
