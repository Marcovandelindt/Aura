@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <a href="{{ route('scambaiter.show', $conversation) }}" class="back-button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                    </svg>
                    Back to Conversation
                </a>
            </div>
            <h1>Edit: {{ $conversation->title }}</h1>
            <ul class="breadcrumb">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li><a href="{{ route('scambaiter.index') }}">Scambaiter</a></li>
                <li><a href="{{ route('scambaiter.show', $conversation) }}">{{ $conversation->title }}</a></li>
                <li>Edit</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('scambaiter.update', $conversation) }}">
        @csrf
        @method('PUT')
        @include('scambaiter._form', ['isEditing' => true])
    </form>
</div>
@endsection
