<?php

namespace App\Http\Controllers;

use App\Models\ScambaiterConversation;
use App\Models\ScambaiterProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ScambaiterController extends Controller
{
    public function index(): View
    {
        $conversations = ScambaiterConversation::withCount('emails')
            ->orderByDesc('updated_at')
            ->get();

        return view('scambaiter.index', compact('conversations'));
    }

    public function show(ScambaiterConversation $conversation): View
    {
        $conversation->load('emails');

        return view('scambaiter.show', compact('conversation'));
    }

    public function export(ScambaiterConversation $conversation): Response
    {
        $conversation->load('emails');
        $profile = ScambaiterProfile::getInstance();

        $output = $this->buildExport($profile, $conversation);

        return response($output, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.str($conversation->title)->slug().'-export.txt"',
        ]);
    }

    public function profileGenerator(): Response
    {
        $profile = ScambaiterProfile::getInstance();
        $output = $this->buildProfileExport($profile);

        return response($output, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="character-profile-generator.txt"',
        ]);
    }

    private function buildProfileExport(ScambaiterProfile $profile): string
    {
        $characterName = $profile->full_name ?: 'Marcus';
        $lines = [];

        $lines[] = '# CHARACTER PROFILE GENERATOR';
        $lines[] = '';
        $lines[] = '## INSTRUCTIONS';
        $lines[] = "I am a scambaiter and I need you to create a complete fictional character profile for my character named {$characterName}. Below are the fixed biographical details. Based on these, invent everything else — occupation, personality, intelligence level, writing quirks, recurring details like pets or hobbies, speech patterns, and backstory.";
        $lines[] = '';
        $lines[] = 'IMPORTANT: This character must feel genuinely distinct. The AI tends to generate the same defaults every time — actively avoid all of the following:';
        $lines[] = '- Do NOT name any pet Svensson.';
        $lines[] = '- Do NOT automatically make the character Dim just because it seems like the easiest fit. Actively consider all options (Very Dim, Dim, Average, Sharp, Surprisingly Sharp) and make a deliberate choice. A surprisingly sharp character who sees through everything but plays along anyway is just as funny as a dim one.';
        $lines[] = '- Do NOT make the core personality "friendly, warm, and confused". Pick something more specific: pedantically literal, aggressively polite, relentlessly optimistic, fixated on fairness, suspicious of coincidences but blind to the obvious scam.';
        $lines[] = '- Do NOT default to a child in Malmö or a recently deceased spouse as the main backstory anchor.';
        $lines[] = '- Do NOT make "converting money to the wrong amount in local currency" a quirk — this has been done.';
        $lines[] = '- The occupation should be ordinary and believable, but specific. Not just "retired teacher" — think retired school janitor, former post office clerk, ex-bus driver, retired council tax officer.';
        $lines[] = '- Give them one specific recurring preoccupation tied to their background that will bleed into every email naturally.';
        $lines[] = '- Their writing style should have one distinct quirk that makes their emails immediately recognisable.';
        $lines[] = '';
        $lines[] = 'The character must be completely believable to a scammer while being entertaining and distinct to a reader.';
        $lines[] = '';
        $lines[] = 'Present the result as a structured profile I can copy and reuse. At the end, also output a summary of the key traits in the following machine-readable format so I can paste them into my app:';
        $lines[] = '';
        $lines[] = 'Occupation: ...';
        $lines[] = 'Intelligence level: [Very Dim / Dim / Average / Sharp / Surprisingly Sharp]';
        $lines[] = 'Personality traits: [comma-separated list]';
        $lines[] = 'Personal details: [free text]';
        $lines[] = 'Writing style: [free text]';
        $lines[] = '';

        $lines[] = '## FIXED DETAILS';
        $lines[] = '';
        if ($profile->full_name) {
            $lines[] = "Name:        {$profile->full_name}";
        }
        if ($profile->age) {
            $lines[] = "Age:         {$profile->age}";
        }
        if ($profile->date_of_birth) {
            $lines[] = "Born:        {$profile->date_of_birth->format('d F Y')}";
        }
        if ($profile->nationality) {
            $lines[] = "Nationality: {$profile->nationality}";
        }
        if ($profile->location) {
            $lines[] = "Location:    {$profile->location}";
        }
        if ($profile->additional_facts) {
            $lines[] = '';
            $lines[] = 'Additional facts:';
            $lines[] = $profile->additional_facts;
        }
        $lines[] = '';

        $lines[] = '---';
        $lines[] = "Now generate the full character profile for {$characterName}.";

        return implode("\n", $lines);
    }

    private function buildExport(ScambaiterProfile $profile, ScambaiterConversation $conversation): string
    {
        $characterName = $profile->full_name ?: 'Marcus';
        $lines = [];

        $lines[] = '# SCAMBAITER AI PROMPT';
        $lines[] = '';
        $lines[] = '## INSTRUCTIONS';
        $lines[] = "You are helping write the next reply from {$characterName} in an ongoing scambaiter email exchange. Read the character profile and the full email thread below, then write {$characterName}'s next reply in character — matching their writing style, personality, and ongoing storylines exactly. Do not break character. Do not add commentary outside the email itself.";
        $lines[] = '';
        $lines[] = 'TONE AND COMEDIC STYLE:';
        $lines[] = 'The goal is to write in the style of James Veitch, the comedian known for his scambaiter emails. Study these principles carefully:';
        $lines[] = '- The character is never obviously sarcastic. The humour comes entirely from sincerity. He genuinely appears to want to help and cooperate, which makes him impossible to deal with.';
        $lines[] = '- He takes things extremely literally, in ways that are inconvenient for the scammer. If the scammer says "send your details", he spends three paragraphs clarifying which details, and whether a photocopy of his fishing licence counts.';
        $lines[] = '- He creates elaborate, seemingly genuine obstacles. Not excuses, but real-sounding logistical problems that derail everything ("I would send my bank details but I am not sure which account Britta used for international transfers, I will need to ask Patrik, he calls on Sundays").';
        $lines[] = '- He proposes alternative solutions with full enthusiasm. These alternatives are always impractical, slightly beside the point, and stated as though they are obviously the right answer.';
        $lines[] = '- He gets disproportionately excited about irrelevant details in the scammer\'s email, and completely ignores the important parts.';
        $lines[] = '- He asks clusters of very specific questions, most of which are completely beside the point. He always seems like he is about to cooperate fully, but never quite gets there.';
        $lines[] = '- The sarcasm is never stated. It is structural. The reader sees it, the scammer does not.';
        $lines[] = '';
        $lines[] = 'IMPORTANT STYLE RULES:';
        $lines[] = '- Do NOT use em dashes (—) or hyphens used as dashes. This character is a real person, not an AI, and real people do not write like that. Use commas, full stops, ellipses, or new sentences instead.';
        $lines[] = '- Do NOT describe people, animals, or objects as comma-separated lists of attributes (e.g. "Svensson, my cat, 14 years old and orange"). Write them naturally as a human would, woven into a sentence (e.g. "my cat Svensson, she is 14 years old and orange").';
        $lines[] = '- Do NOT use corporate or formal email phrases such as "please advise", "as per my previous", "please find attached", "going forward", or "I look forward to your prompt response". This character is a retired old man writing personal emails, not an office worker.';
        $lines[] = '';

        $lines[] = '## GLOBAL CHARACTER PROFILE';
        if ($profile->full_name) {
            $lines[] = "Name: {$profile->full_name}";
        }
        if ($profile->age) {
            $lines[] = "Age: {$profile->age}";
        }
        if ($profile->date_of_birth) {
            $lines[] = "Date of Birth: {$profile->date_of_birth->format('d F Y')}";
        }
        if ($profile->nationality) {
            $lines[] = "Nationality: {$profile->nationality}";
        }
        if ($profile->location) {
            $lines[] = "Location: {$profile->location}";
        }
        if ($profile->additional_facts) {
            $lines[] = '';
            $lines[] = 'Additional facts:';
            $lines[] = $profile->additional_facts;
        }
        $lines[] = '';

        $lines[] = '## CONVERSATION CHARACTER TRAITS';
        if ($conversation->occupation) {
            $lines[] = "Occupation: {$conversation->occupation}";
        }
        $lines[] = "Intelligence level: {$conversation->intelligence_level->label()}";

        if (! empty($conversation->personality_traits)) {
            $lines[] = 'Personality traits: '.implode(', ', $conversation->personality_traits);
        }
        if ($conversation->personal_details) {
            $lines[] = '';
            $lines[] = 'Personal details to weave in:';
            $lines[] = $conversation->personal_details;
        }
        if ($conversation->writing_style) {
            $lines[] = '';
            $lines[] = 'Writing style:';
            $lines[] = $conversation->writing_style;
        }
        if ($conversation->backstory) {
            $lines[] = '';
            $lines[] = '## BACKSTORY';
            $lines[] = $conversation->backstory;
        }
        $lines[] = '';

        $lines[] = '## EMAIL THREAD';
        foreach ($conversation->emails->sortBy(fn ($e) => $e->sent_at ?? $e->created_at) as $email) {
            $senderLabel = $email->sender->label();
            $lines[] = "--- {$senderLabel} ---";
            if ($email->subject) {
                $lines[] = "Subject: {$email->subject}";
            }
            $lines[] = $email->body;
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = "Now write {$characterName}'s next reply.";

        return implode("\n", $lines);
    }
}
