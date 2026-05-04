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

        $occupation = $this->randomOccupation();
        $intelligence = $this->randomIntelligence();
        $personality = $this->randomPersonality();
        $writingQuirk = $this->randomWritingQuirk();
        $preoccupation = $this->randomPreoccupation();

        $lines[] = '# CHARACTER PROFILE GENERATOR';
        $lines[] = '';
        $lines[] = '## INSTRUCTIONS';
        $lines[] = "I am a scambaiter and I need you to create a complete fictional character profile for my character named {$characterName}. The fixed biographical details are below. The key character dimensions have been pre-selected — you MUST use them exactly as specified. Do not substitute, soften, or approximate. Build everything else (backstory, personal details, writing style, recurring story elements) around these requirements.";
        $lines[] = '';
        $lines[] = '## PRE-SELECTED CHARACTER DIMENSIONS';
        $lines[] = 'These have been randomly chosen. You must honour them exactly.';
        $lines[] = '';
        $lines[] = "OCCUPATION: {$occupation['role']}";
        $lines[] = "This is the character's former job. Use it specifically — not a vague approximation. Their background in this work should bleed naturally into everything: the way they think, the comparisons they make, the things they notice.";
        $lines[] = "Preoccupation that bleeds from this background: {$occupation['preoccupation']}";
        $lines[] = '';
        $lines[] = "INTELLIGENCE LEVEL: {$intelligence['level']}";
        $lines[] = $intelligence['description'];
        $lines[] = '';
        $lines[] = "PERSONALITY CORE: {$personality['name']}";
        $lines[] = $personality['description'];
        $lines[] = '';
        $lines[] = "WRITING QUIRK: {$writingQuirk['name']}";
        $lines[] = $writingQuirk['description'];
        $lines[] = 'This quirk must be present and recognisable in the example emails you include in the profile.';
        $lines[] = '';
        $lines[] = "RECURRING PREOCCUPATION: {$preoccupation}";
        $lines[] = 'This is a personal life detail that keeps coming up in emails — not because it is relevant, but because it is on their mind.';
        $lines[] = '';
        $lines[] = '## THINGS TO AVOID REGARDLESS';
        $lines[] = '- Do NOT name any pet Svensson.';
        $lines[] = '- Do NOT default to a recently deceased spouse as the main backstory anchor.';
        $lines[] = '- Do NOT make "converting money to the wrong amount in local currency" a recurring quirk.';
        $lines[] = '- Do NOT make emails feel like internal memos, official reports, or structured around reference numbers — this has been done.';
        $lines[] = '- Do NOT use em dashes (—). Real people do not write like that.';
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

        $lines[] = '## FIXED BIOGRAPHICAL DETAILS';
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
        $lines[] = "Now generate the full character profile for {$characterName}. Honour all pre-selected dimensions exactly.";

        return implode("\n", $lines);
    }

    /** @return array{role: string, preoccupation: string} */
    private function randomOccupation(): array
    {
        $occupations = [
            ['role' => 'Retired long-distance lorry driver (drove international freight routes across Europe for 30+ years)', 'preoccupation' => 'distances, routes, border crossings, rest stop logistics, and whether a given journey is "realistic" given the time involved'],
            ['role' => 'Former ferry deckhand (worked passenger ferries between Sweden and Germany/Denmark for 25 years)', 'preoccupation' => 'tides, weather windows, loading procedures, and an instinctive distrust of landlocked people\'s sense of time'],
            ['role' => 'Retired school caretaker (maintained a secondary school building for 28 years)', 'preoccupation' => 'whether things are properly locked, who has access to what, and a compulsive habit of noticing when something is broken or out of place'],
            ['role' => 'Former post office sorting clerk (sorted and processed mail at a regional depot for 22 years)', 'preoccupation' => 'correct addressing, postage costs, delivery times, and a deep scepticism about anything that claims to be official mail'],
            ['role' => 'Retired amateur radio operator and former volunteer coast guard radio monitor', 'preoccupation' => 'signal clarity, frequencies, whether communications are being intercepted, and a habit of giving phonetic alphabet letters when spelling things out'],
            ['role' => 'Ex-fisherman (commercial inshore fishing on the west coast of Sweden for 20 years, now retired)', 'preoccupation' => 'weather forecasts, tides, the price of fuel, and an intimate familiarity with waiting for things that may or may not arrive'],
            ['role' => 'Retired small-town pharmacist\'s dispensing assistant (worked in the same pharmacy for 31 years)', 'preoccupation' => 'dosages, contraindications, expiry dates, and whether something has been properly labelled and stored'],
            ['role' => 'Former hospital porter (transported patients and equipment around a large regional hospital for 27 years)', 'preoccupation' => 'which route is fastest, who is actually in charge of what, and a calm, institutional indifference to anything that sounds dramatic'],
            ['role' => 'Retired forestry worker (planted and maintained state forests in central Sweden for 24 years)', 'preoccupation' => 'how long things actually take to grow, the difference between what something looks like now and what it will become, and deep scepticism about quick results'],
            ['role' => 'Ex-cinema projectionist (ran the projection booth at a local cinema from 1978 to 2009)', 'preoccupation' => 'timing, synchronisation, whether the picture and the sound match up, and an habit of narrating scenes as if describing a film'],
            ['role' => 'Retired butcher (ran a small butcher\'s shop for 26 years before the supermarkets drove him out)', 'preoccupation' => 'exact weights, correct cuts, fair pricing, and a lingering bitterness about large organisations squeezing out the small ones'],
            ['role' => 'Former groundskeeper at a municipal sports complex (maintained pitches, courts and equipment for 29 years)', 'preoccupation' => 'surface conditions, maintenance schedules, and an instinctive distrust of anyone who doesn\'t look after their equipment properly'],
        ];

        return $occupations[array_rand($occupations)];
    }

    /** @return array{level: string, description: string} */
    private function randomIntelligence(): array
    {
        $levels = [
            ['level' => 'Very Dim', 'description' => 'Genuinely struggles to follow multi-step reasoning. Does not notice contradictions. Accepts implausible explanations at face value. Not malicious — just not equipped. Misunderstands things in ways that are specific and consistent, not random.'],
            ['level' => 'Dim', 'description' => 'Gets there eventually but takes the scenic route. Misses implications but understands direct statements. Can be led in circles by patient repetition. Often confident about the wrong conclusion.'],
            ['level' => 'Average', 'description' => 'Perfectly normal, unremarkable reasoning. Neither sharp nor dim. Falls for the scam not because of stupidity but because of trust, loneliness, or wishful thinking. The most human option.'],
            ['level' => 'Sharp', 'description' => 'Actually quite perceptive — but applies it to completely the wrong things. Notices minor inconsistencies in dates or names while completely missing the obvious. Their intelligence makes them harder to deal with, not easier.'],
            ['level' => 'Surprisingly Sharp', 'description' => 'Sees through the scam almost immediately — but plays along anyway, out of curiosity, boredom, or a genuine desire to waste the scammer\'s time. The comedy comes from how carefully they manage this performance.'],
        ];

        return $levels[array_rand($levels)];
    }

    /** @return array{name: string, description: string} */
    private function randomPersonality(): array
    {
        $personalities = [
            ['name' => 'Relentlessly optimistic', 'description' => 'Genuinely believes everything will work out fine. Not naive — just constitutionally unable to dwell on bad outcomes. Responds to red flags with cheerful reframing. Cannot be panicked or rushed because panic simply doesn\'t stick.'],
            ['name' => 'Chronically indignant', 'description' => 'Mildly outraged by small things (incorrect postage, unnecessary fees, vague wording) while remaining completely unbothered by large ones (inheriting money from a stranger, being asked for their bank details). The proportion is always wrong.'],
            ['name' => 'Compulsively hospitable', 'description' => 'Keeps trying to invite people over, send them things, or offer practical help that is impossible to accept. Every email contains an offer of food, a spare room, or assistance with something logistical. Cannot receive information without immediately thinking about how to host the sender.'],
            ['name' => 'Obsessively fair-minded', 'description' => 'Cannot proceed without everything being perfectly equal and agreed upon. Insists on splitting things fairly, documenting agreements, and making sure no one is getting a bad deal — including, apparently, the scammer.'],
            ['name' => 'Catastrophically distractible', 'description' => 'Starts every reply intending to address the scammer\'s points but gets derailed by something that happened that morning, something the neighbour said, a problem with the boiler. Always returns to the subject, eventually, with an apology.'],
            ['name' => 'Stubbornly principled about one specific thing', 'description' => 'Has one non-negotiable personal rule — tied to their background — that keeps getting in the way of everything. Not generally difficult, but immovable on this one point. The rule sounds reasonable; the application is consistently catastrophic.'],
            ['name' => 'Genuinely kind but completely oblivious', 'description' => 'Warm and sincerely helpful in ways that are entirely unhelpful. Tries hard to make things easier and succeeds only in making them more complicated. Not dim — just socially wired in a way that prioritises good intentions over outcomes.'],
            ['name' => 'Suspicious of the wrong things', 'description' => 'Paranoid about specific irrelevant details (whether an email address looks "official", whether the font seems trustworthy) while utterly blind to the actual scam. Has developed an elaborate but entirely useless fraud-detection system based on past experience.'],
            ['name' => 'Relentlessly practical', 'description' => 'Ignores all emotional or dramatic content and focuses only on logistics. If told they\'ve inherited a fortune, their first question is about shipping costs. Processes everything as a project to be organised, never as something to feel things about.'],
            ['name' => 'Quietly competitive', 'description' => 'Subtly turns everything into a comparison. Whatever the scammer claims — money, status, connections — Marcus has a cousin who had something similar, or knew someone who did something better. Never boastful, just constantly reframing things in a way that slightly diminishes the scammer.'],
        ];

        return $personalities[array_rand($personalities)];
    }

    /** @return array{name: string, description: string} */
    private function randomWritingQuirk(): array
    {
        $quirks = [
            ['name' => 'Opens every email with a weather or situation report', 'description' => 'Before addressing anything, the character describes the current conditions — weather, what they were doing, how they are feeling physically. Always specific, always unnecessary. Gets to the point eventually.'],
            ['name' => 'Refers constantly to a specific third person for validation', 'description' => 'A neighbour, a son, a friend from the old job — this person is consulted about everything and quoted frequently. "I mentioned this to Rolf and he said..." The third person\'s opinions are treated as highly authoritative.'],
            ['name' => 'Writes in very short, abrupt sentences', 'description' => 'One thought per sentence. Full stop. New sentence. No flow between ideas. Reads like a telegram. The effect is oddly authoritative, even when the content is completely off the point.'],
            ['name' => 'Gets disproportionately excited about one irrelevant detail per email', 'description' => 'Something in the scammer\'s email — a word, a place name, an incidental detail — triggers a long digression. The actual content of the email is addressed briefly at the end, if at all.'],
            ['name' => 'Signs off differently every time with an elaborate or oddly personal closing', 'description' => 'The sign-off is never just "Regards". It changes every email and is always slightly too personal, too formal, or too specific to the circumstances. "Yours in anticipation, Marcus Lindsson (currently in good health)."'],
            ['name' => 'Structures everything as a numbered list, even when it makes no sense', 'description' => 'Questions, updates, thoughts, and observations are all numbered. Even a single item becomes "(1)". If there are follow-up thoughts they become "(1a)" and "(1b)". The list format never helps.'],
            ['name' => 'Always summarises previous correspondence at the start, slightly wrong', 'description' => 'Opens by recapping what has been agreed or established so far — but gets a detail subtly wrong each time. Never corrects itself. The cumulative drift becomes significant over a long exchange.'],
            ['name' => 'Includes a completely irrelevant personal update in every email as if it belongs there', 'description' => 'Somewhere in the middle of every email, without transition, there is a brief update about something unrelated to the scam: the result of a medical appointment, a problem with the guttering, a note about what was on television. Then continues as if nothing happened.'],
            ['name' => 'Always mentions what they were physically doing when they read the email', 'description' => '"I was eating breakfast when your message arrived." "I read this standing in the hall with my coat still on." Grounds every reply in a specific domestic moment. The specificity is completely unnecessary and somehow endearing.'],
            ['name' => 'Uses a consistent malapropism or slightly wrong word throughout', 'description' => 'One specific word or phrase is always slightly off — not random errors, but one consistent substitution that recurs. They mean well. The meaning is usually recoverable. It is never corrected.'],
        ];

        return $quirks[array_rand($quirks)];
    }

    private function randomPreoccupation(): string
    {
        $preoccupations = [
            'An ongoing dispute with a neighbour about a shared fence, hedge, or parking arrangement that has been running for years and shows no sign of resolution',
            'A home repair project (leaking roof, broken boiler, damp wall) that multiple tradespeople have assessed but nobody has actually fixed yet',
            'Preparing for an upcoming event — a family visit, a birthday, a local club gathering — with logistical detail that suggests it is weeks away but is actually quite soon',
            'A minor recurring health issue (bad knee, poor sleep, a cough that won\'t go away) mentioned matter-of-factly, never dramatically, in almost every email',
            'A local institution — a post office, a library, a bus route — that is being closed or changed, which they are resigned to but mildly aggrieved about',
            'An ongoing attempt to learn or master something new (a recipe, a piece of technology, a board game) that keeps going slightly wrong',
            'A garden, allotment, or balcony plant that is either doing unexpectedly well or refusing to cooperate, depending on the season',
            'A television programme or radio serial they follow closely and occasionally reference as if the other person also watches it',
            'A friend or former colleague who recently moved away, retired, or became ill — mentioned with the mild sadness of someone adjusting to a smaller world',
            'An administrative task they have been meaning to complete for months (renewing something, filing something, cancelling something) that keeps being deferred',
        ];

        return $preoccupations[array_rand($preoccupations)];
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
