@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <a href="{{ route('scambaiter.index') }}" class="back-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                        </svg>
                        Back to Scambaiter
                    </a>
                </div>
                <h1>{{ $conversation->title }}</h1>
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}">Home</a></li>
                    <li><a href="{{ route('scambaiter.index') }}">Scambaiter</a></li>
                    <li>{{ $conversation->title }}</li>
                </ul>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="{{ route('scambaiter.edit', $conversation) }}" class="btn btn-secondary">
                    <i class="fas fa-pen"></i> Edit
                </a>
                <a href="{{ route('scambaiter.export', $conversation) }}" class="btn btn-secondary">
                    <i class="fas fa-file-export"></i> Export for AI
                </a>
                <form method="POST" action="{{ route('scambaiter.destroy', $conversation) }}" onsubmit="return confirm('Delete this entire conversation?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start;">

        {{-- Email Thread --}}
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                    <h3>Email Thread</h3>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 0.8rem; color: #888;">{{ $conversation->emails->count() }} email{{ $conversation->emails->count() === 1 ? '' : 's' }}</span>
                        @if($conversation->emails->count() > 1)
                            <button type="button" onclick="toggleAllEmails()" id="toggle-all-btn" style="background: none; border: none; cursor: pointer; font-size: 0.8rem; color: var(--primary-color); padding: 0;">
                                Collapse all
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    @if($conversation->emails->isEmpty())
                        <div style="text-align: center; padding: 3rem 2rem; color: #888;">
                            <i class="fas fa-envelope" style="font-size: 2rem; margin-bottom: 0.75rem; color: #ccc; display: block;"></i>
                            No emails yet. Add the first one below.
                        </div>
                    @else
                        <div class="scambaiter-thread">
                            @foreach($conversation->emails as $email)
                                <div class="scambaiter-email scambaiter-email-{{ $email->sender->value }}">
                                    <div class="scambaiter-email-header" onclick="toggleEmail(this)" style="cursor: pointer;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                            <span class="scambaiter-sender-badge scambaiter-sender-{{ $email->sender->value }}">
                                                {{ $email->sender->label() }}
                                            </span>
                                            @if($email->subject)
                                                <span style="font-weight: 600; font-size: 0.9rem;">{{ $email->subject }}</span>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                                            <span style="font-size: 0.75rem; color: #888;">{{ $email->created_at->format('M j, Y H:i') }}</span>
                                            <i class="fas fa-chevron-up scambaiter-chevron" style="font-size: 0.75rem; color: #aaa; transition: transform 0.2s;"></i>
                                            <form method="POST" action="{{ route('scambaiter.emails.destroy', [$conversation, $email]) }}" onsubmit="return confirm('Remove this email?')" onclick="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: none; border: none; cursor: pointer; color: #ccc; padding: 0; line-height: 1;" title="Remove email">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="scambaiter-email-body">{{ $email->body }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const emails = document.querySelectorAll('.scambaiter-email');
                        emails.forEach((email, index) => {
                            if (index === emails.length - 1) return;
                            const body = email.querySelector('.scambaiter-email-body');
                            const chevron = email.querySelector('.scambaiter-chevron');
                            body.style.display = 'none';
                            chevron.style.transform = 'rotate(180deg)';
                        });
                    });

                    function toggleEmail(header) {
                        const email = header.closest('.scambaiter-email');
                        const body = email.querySelector('.scambaiter-email-body');
                        const chevron = header.querySelector('.scambaiter-chevron');
                        const collapsed = body.style.display === 'none';

                        body.style.display = collapsed ? '' : 'none';
                        chevron.style.transform = collapsed ? '' : 'rotate(180deg)';
                    }

                    function toggleAllEmails() {
                        const btn = document.getElementById('toggle-all-btn');
                        const allBodies = document.querySelectorAll('.scambaiter-email-body');
                        const anyExpanded = [...allBodies].some(b => b.style.display !== 'none');

                        document.querySelectorAll('.scambaiter-email').forEach(email => {
                            const body = email.querySelector('.scambaiter-email-body');
                            const chevron = email.querySelector('.scambaiter-chevron');

                            body.style.display = anyExpanded ? 'none' : '';
                            chevron.style.transform = anyExpanded ? 'rotate(180deg)' : '';
                        });

                        btn.textContent = anyExpanded ? 'Expand all' : 'Collapse all';
                    }
                </script>
            </div>

            {{-- Add Email Form --}}
            <div class="card">
                <div class="card-header"><h3>Add Email</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('scambaiter.emails.store', $conversation) }}">
                        @csrf

                        <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem;">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Sender <span style="color: #ef4444;">*</span></label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <label class="scambaiter-sender-option">
                                        <input type="radio" name="sender" value="scammer" {{ old('sender', 'scammer') === 'scammer' ? 'checked' : '' }}>
                                        <span class="scambaiter-sender-label scambaiter-sender-label-scammer">
                                            <i class="fas fa-mask"></i> Scammer
                                        </span>
                                    </label>
                                    <label class="scambaiter-sender-option">
                                        <input type="radio" name="sender" value="marcus" {{ old('sender') === 'marcus' ? 'checked' : '' }}>
                                        <span class="scambaiter-sender-label scambaiter-sender-label-marcus">
                                            <i class="fas fa-user-secret"></i> Marcus
                                        </span>
                                    </label>
                                </div>
                                @error('sender')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                            </div>

                            <div style="flex: 2;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject <span style="color: #888; font-weight: 400;">(optional)</span></label>
                                <input type="text" name="subject" value="{{ old('subject', $conversation->subject) }}" class="form-control" placeholder="Re: Urgent Business Proposal">
                                @error('subject')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Body <span style="color: #ef4444;">*</span></label>
                            <textarea name="body" class="form-control" rows="8" placeholder="Paste the email content here..." required>{{ old('body') }}</textarea>
                            @error('body')<p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add to Thread
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Character Profile --}}
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Character Profile</h3>
                    @if($conversation->is_locked)
                        <span class="scambaiter-badge scambaiter-badge-locked" style="font-size: 0.7rem;">
                            <i class="fas fa-lock"></i> Locked
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($conversation->subject)
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.25rem;">Default Subject</div>
                            <p style="font-size: 0.85rem; margin: 0;">{{ $conversation->subject }}</p>
                        </div>
                    @endif

                    @if($conversation->occupation)
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.25rem;">Occupation</div>
                            <p style="font-size: 0.85rem; margin: 0;">{{ $conversation->occupation }}</p>
                        </div>
                    @endif

                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.25rem;">Intelligence</div>
                        <span class="scambaiter-badge scambaiter-badge-intelligence">{{ $conversation->intelligence_level->label() }}</span>
                    </div>

                    @if(!empty($conversation->personality_traits))
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.5rem;">Personality</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                @foreach($conversation->personality_traits as $trait)
                                    <span class="scambaiter-badge scambaiter-badge-trait">{{ $trait }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($conversation->personal_details)
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.25rem;">Personal Details</div>
                            <p style="font-size: 0.85rem; margin: 0; white-space: pre-wrap;">{{ $conversation->personal_details }}</p>
                        </div>
                    @endif

                    @if($conversation->writing_style)
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #888; margin-bottom: 0.25rem;">Writing Style</div>
                            <p style="font-size: 0.85rem; margin: 0; white-space: pre-wrap;">{{ $conversation->writing_style }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Export --}}
            <div class="card">
                <div class="card-body">
                    <h3 style="margin: 0 0 0.5rem; font-size: 0.95rem;">Export for AI</h3>
                    <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">
                        Generates a ready-made prompt with the full character profile and email thread for pasting into Claude or any other AI.
                    </p>
                    <a href="{{ route('scambaiter.export', $conversation) }}" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-file-export"></i> Download Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
