<?php

namespace App\Support\Chatbot;

use App\Models\Appointment;
use App\Models\BillingAccount;
use App\Models\Client;
use App\Models\MaintenanceRequest;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Support\ServiceArea;

/**
 * The assistant behind the chat bubble on the site and the Assistant screen in
 * the mobile app.
 *
 * There is one copy of it on purpose. Both platforms POST a message here and
 * render whatever comes back, so the two can never answer the same question
 * differently — the mobile app carries no rules of its own.
 *
 * It is deliberately not a language model. Every number it quotes — a balance,
 * a due date, a booking — is read out of the database on the spot, so it cannot
 * invent one, and it costs nothing per message. The trade is that it only knows
 * what is listed in INTENTS; anything else gets an honest "I don't know that"
 * and a route to a human.
 *
 * It also never writes. The customer is always pointed at the screen that does
 * the thing, which is why every answer may carry a `link` but no answer has a
 * side effect.
 */
class Assistant
{
    public const WEB = 'web';
    public const APP = 'app';

    /**
     * Which client is asking.
     *
     * It matters for more than wording. Filing a fault report is a feature of
     * the mobile app only — the website has no client-facing support page, just
     * POST /api/v1/maintenance — so on the web the assistant says where that
     * lives instead of pointing at a page that does not exist.
     */
    private string $platform = self::WEB;

    /**
     * Statuses that mean a maintenance report is finished with.
     */
    private const CLOSED_TICKET_STATUSES = ['resolved', 'completed', 'closed', 'cancelled', 'rejected'];

    /**
     * What each intent listens for.
     *
     * `phrases` are whole expressions and score five; `keywords` are single
     * words and score one. The weighting is what keeps "how do I pay" (a
     * phrase under how_to_pay) away from balance, which also lists "pay" as a
     * keyword. Cebuano and Tagalog triggers are in here alongside the English
     * ones because most of Bantayan asks in those first.
     */
    private const INTENTS = [
        'balance' => [
            'phrases' => [
                'how much do i owe', 'how much do i need to pay', 'my balance', 'outstanding balance',
                'my bill', 'my billing', 'amount due', 'do i have any unpaid', 'pila akong bayronon',
                'magkano ang bayad ko', 'pila ako utang',
            ],
            'keywords' => [
                'balance', 'bill', 'billing', 'owe', 'unpaid', 'statement', 'arrears',
                'bayronon', 'utang', 'bayad',
            ],
        ],
        'next_appointment' => [
            'phrases' => [
                'next appointment', 'my next visit', 'when is my installation', 'when are you coming',
                'when is my appointment', 'my schedule', 'kanus-a ang installation', 'kailan ang schedule ko',
            ],
            'keywords' => ['appointment', 'visit', 'schedule', 'installation', 'iskedyul'],
        ],
        'bookings' => [
            'phrases' => [
                'my bookings', 'all my bookings', 'booking status', 'status of my booking',
                'my requests', 'my appointments',
            ],
            'keywords' => ['bookings', 'booked'],
        ],
        'tickets' => [
            'phrases' => [
                'my report', 'my reports', 'my ticket', 'my tickets', 'status of my report',
                'did you fix', 'follow up', 'my complaint',
            ],
            'keywords' => ['ticket', 'complaint', 'reklamo'],
        ],
        'account_number' => [
            'phrases' => ['my account number', 'what is my account number', 'account no'],
            'keywords' => [],
        ],
        'my_details' => [
            'phrases' => [
                'my details', 'my profile', 'my address', 'my information', 'what address do you have',
                'my contact number', 'my registered',
            ],
            'keywords' => ['profile'],
        ],
        'plans' => [
            'phrases' => [
                'what plans', 'available plans', 'internet plans', 'how much is the plan',
                'what packages', 'subscription', 'how fast', 'unsa ang plano', 'magkano ang plan',
                'price list', 'how much per month',
            ],
            'keywords' => ['plan', 'plans', 'package', 'mbps', 'speed', 'price', 'presyo', 'plano', 'rate'],
        ],
        'coverage' => [
            'phrases' => [
                'do you cover', 'is it available in', 'service area', 'coverage area',
                'do you serve', 'abot ba', 'saklaw',
            ],
            'keywords' => ['coverage', 'area', 'available'],
        ],
        'how_to_book' => [
            'phrases' => [
                'how do i book', 'how to book', 'how do i apply', 'how to apply', 'how do i subscribe',
                'i want to apply', 'i want internet', 'paunsa mag apply', 'paano mag apply',
                'request installation',
            ],
            'keywords' => ['apply', 'book'],
        ],
        'how_to_pay' => [
            'phrases' => [
                'how do i pay', 'how to pay', 'where do i pay', 'where to pay', 'payment method',
                'can i pay', 'accept gcash', 'asa mubayad', 'saan magbayad', 'mode of payment',
            ],
            'keywords' => ['gcash', 'cash', 'payment', 'pay'],
        ],
        'report_fault' => [
            'phrases' => [
                'no internet', 'internet is down', 'not working', 'very slow', 'keeps disconnecting',
                'cannot connect', 'no signal', 'walay internet', 'hinay ang internet', 'walang internet',
                'report a problem', 'i have a problem', 'my connection',
            ],
            'keywords' => ['slow', 'down', 'offline', 'disconnected', 'problem', 'hinay', 'guba'],
        ],
        'installation_time' => [
            'phrases' => [
                'how long does installation take', 'how long is the installation',
                'how long will it take', 'duration of installation', 'what time slots',
                'available times', 'unsa ka dugay',
            ],
            'keywords' => ['slots', 'timeslot'],
        ],
        'contact' => [
            'phrases' => [
                'contact number', 'phone number', 'office hours', 'where is your office',
                'talk to a person', 'talk to someone', 'speak to staff', 'customer service',
                'office address',
            ],
            'keywords' => ['contact', 'hotline', 'office'],
        ],
        'greeting' => [
            'phrases' => ['good morning', 'good afternoon', 'good evening', 'maayong buntag', 'maayong hapon'],
            'keywords' => ['hi', 'hello', 'hey', 'kumusta', 'kamusta'],
        ],
        'thanks' => [
            'phrases' => ['thank you', 'thanks a lot', 'daghang salamat'],
            'keywords' => ['thanks', 'salamat'],
        ],
        'bye' => [
            'phrases' => ['good bye', 'see you'],
            'keywords' => ['bye', 'goodbye'],
        ],
        'help' => [
            'phrases' => [
                'what can you do', 'what can i ask', 'can you help', 'help me', 'unsa imong mahimo',
            ],
            'keywords' => ['help', 'tabang'],
        ],
    ];

    /**
     * The reply for a message.
     *
     * Passing null or an empty message returns the opening greeting, which is
     * what both clients ask for when the chat is first opened.
     *
     * @return array{reply:string,intent:string,suggestions:array<int,string>,link:?array}
     */
    public function respond(?string $message, ?Client $client, string $platform = self::WEB): array
    {
        $this->platform = $platform === self::APP ? self::APP : self::WEB;

        $text = $this->normalise((string) $message);

        if ($text === '') {
            return $this->opening($client, $this->platform);
        }

        return match ($this->match($text)) {
            'balance'           => $this->balance($client),
            'next_appointment'  => $this->nextAppointment($client),
            'bookings'          => $this->bookings($client),
            'tickets'           => $this->tickets($client),
            'account_number'    => $this->accountNumber($client),
            'my_details'        => $this->myDetails($client),
            'plans'             => $this->plans($client),
            'coverage'          => $this->coverage($client),
            'how_to_book'       => $this->howToBook($client),
            'how_to_pay'        => $this->howToPay($client),
            'report_fault'      => $this->reportFault($client),
            'installation_time' => $this->installationTime($client),
            'contact'           => $this->contact($client),
            'greeting'          => $this->opening($client),
            'thanks'            => $this->simple('thanks', 'Anytime! Anything else I can check for you?', $client),
            'bye'               => $this->simple('bye', 'Thanks for dropping by. The chat is here whenever you need it.', $client),
            'help'              => $this->help($client),
            default             => $this->fallback($client),
        };
    }

    /** The greeting both clients show before the customer has typed anything. */
    public function opening(?Client $client, string $platform = self::WEB): array
    {
        $this->platform = $platform === self::APP ? self::APP : self::WEB;

        $name = $client?->firstname;

        return [
            'reply' => $name
                ? "Hi {$name}! I'm the BCTVI assistant. I can check your balance, your bookings and your reports, or explain how something works."
                : "Hi! I'm the BCTVI assistant. I can tell you about our plans, our coverage and how to sign up. Sign in and I can also check your balance and bookings.",
            'intent'      => 'greeting',
            'suggestions' => $this->suggestionsFor($client),
            'link'        => null,
        ];
    }

    // ─── Matching ────────────────────────────────────────────────────────────

    /**
     * Lowercase, strip punctuation and collapse whitespace, so "How much do I
     * owe??" and "how much do i owe" are the same question.
     */
    private function normalise(string $message): string
    {
        $message = mb_strtolower(trim($message));
        $message = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $message) ?? '';

        return trim(preg_replace('/\s+/', ' ', $message) ?? '');
    }

    /**
     * The best-scoring intent, or null when nothing clears the bar.
     *
     * A single keyword is enough to match — the fallback is cheap and a wrong
     * guess costs the customer one tap — but any phrase outweighs a pile of
     * them, which is what separates intents that share vocabulary.
     *
     * A phrase is worth four points per word rather than a flat score, because
     * the longer of two matching phrases is the more specific one. That is what
     * decides "my contact number", where my_details matches three words and
     * contact matches the two inside them.
     */
    private function match(string $text): ?string
    {
        $best = null;
        $bestScore = 0;

        foreach (self::INTENTS as $intent => $triggers) {
            $score = 0;

            foreach ($triggers['phrases'] as $phrase) {
                if (str_contains($text, $phrase)) {
                    $score += 4 * count(explode(' ', $phrase));
                }
            }

            foreach ($triggers['keywords'] as $keyword) {
                // Word-boundary matched, so "pay" does not fire inside an
                // unrelated word, with a trailing plural allowed so a list does
                // not have to carry both "plan" and "plans".
                if (preg_match('/\b' . preg_quote($keyword, '/') . '(s|es)?\b/u', $text) === 1) {
                    $score += 1;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $intent;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    // ─── Answers that read the customer's own record ─────────────────────────

    private function balance(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('balance', 'your balance');
        }

        $statements = BillingAccount::where('client_id', $client->id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();

        if ($statements->isEmpty()) {
            return $this->answer(
                'balance',
                "You're all paid up — there's nothing outstanding on your account right now.",
                $client,
                $this->link('View billing', 'client.billing', 'billing'),
            );
        }

        $total = $statements->sum('total_amount_due');
        $count = $statements->count();
        $earliest = $statements->first()->due_date;

        $reply = 'Your outstanding balance is ' . $this->peso($total) . '. '
            . ($count === 1 ? 'One statement is' : "{$count} statements are") . ' awaiting payment';
        $reply .= $earliest ? ', the earliest due ' . $earliest->format('M j, Y') . '.' : '.';

        return $this->answer(
            'balance',
            $reply,
            $client,
            $this->link('View billing', 'client.billing', 'billing'),
        );
    }

    private function nextAppointment(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('next_appointment', 'your next visit');
        }

        $next = Appointment::with('service')
            ->where('client_id', $client->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('preferred_date', '>=', now()->toDateString())
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->first();

        if (!$next) {
            return $this->answer(
                'next_appointment',
                'You have no upcoming visits booked. You can request an installation or a service visit whenever you like.',
                $client,
                $this->link('Book a visit', 'client.book', 'book'),
            );
        }

        $reply = 'Your next visit is ' . ($next->service->service_name ?? 'a service visit')
            . ' on ' . date('M j, Y', strtotime($next->preferred_date))
            . ' at ' . date('g:i A', strtotime($next->preferred_time))
            . '. Status: ' . ucfirst($next->status) . '.';

        return $this->answer(
            'next_appointment',
            $reply,
            $client,
            $this->link('View bookings', 'client.appointments', 'appointments'),
        );
    }

    private function bookings(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('bookings', 'your bookings');
        }

        $all = Appointment::where('client_id', $client->id)->get();

        if ($all->isEmpty()) {
            return $this->answer(
                'bookings',
                "You haven't booked anything yet. Once you request a visit it will show up here.",
                $client,
                $this->link('Book a visit', 'client.book', 'book'),
            );
        }

        $byStatus = $all->groupBy(fn ($a) => strtolower($a->status))->map->count();
        $parts = [];

        foreach (['pending', 'approved', 'completed', 'cancelled'] as $status) {
            if (($byStatus[$status] ?? 0) > 0) {
                $parts[] = $byStatus[$status] . ' ' . $status;
            }
        }

        $reply = 'You have ' . $all->count() . ' ' . ($all->count() === 1 ? 'booking' : 'bookings')
            . ($parts ? ': ' . implode(', ', $parts) . '.' : '.');

        return $this->answer(
            'bookings',
            $reply,
            $client,
            $this->link('View bookings', 'client.appointments', 'appointments'),
        );
    }

    private function tickets(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('tickets', 'your reports');
        }

        $open = MaintenanceRequest::where('client_id', $client->id)
            ->whereNotIn('status', self::CLOSED_TICKET_STATUSES)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($open->isEmpty()) {
            return $this->answer(
                'tickets',
                'You have no open reports at the moment. If something is wrong with your connection you can file one and the team will pick it up.',
                $client,
                $this->supportLink(),
            );
        }

        $first = $open->first();
        $reply = 'You have ' . $open->count() . ' open ' . ($open->count() === 1 ? 'report' : 'reports')
            . '. The latest is "' . $first->subject . '" — status ' . ucfirst($first->status) . '.';

        if ($first->follow_up_note) {
            $reply .= ' Note from BCTVI: ' . $first->follow_up_note;
        }

        return $this->answer('tickets', $reply, $client, $this->supportLink());
    }

    private function accountNumber(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('account_number', 'your account number');
        }

        return $this->answer(
            'account_number',
            $client->account_number
                ? 'Your BCTVI account number is ' . $client->account_number . '.'
                : "There's no account number on your record yet — the office assigns one when your service is activated.",
            $client,
        );
    }

    private function myDetails(?Client $client): array
    {
        if (!$client) {
            return $this->signInFirst('my_details', 'your registered details');
        }

        $address = collect([$client->address_barangay, $client->address_municipality, $client->address_province])
            ->filter()
            ->implode(', ');

        $reply = 'We have you as ' . trim($client->firstname . ' ' . $client->lastname) . '.';
        $reply .= $address ? ' Address on file: ' . $address . '.' : '';
        $reply .= $client->contact_no ? ' Contact number: ' . $client->contact_no . '.' : '';
        $reply .= ' You can correct any of this yourself from your account page.';

        return $this->answer(
            'my_details',
            $reply,
            $client,
            $this->link('Edit my details', 'client.dashboard', 'profile'),
        );
    }

    // ─── Answers about the service itself ────────────────────────────────────

    private function plans(?Client $client): array
    {
        $services = Service::active()->orderBy('price')->get();

        if ($services->isEmpty()) {
            return $this->answer(
                'plans',
                'There are no plans listed at the moment. The office can tell you what is currently on offer.',
                $client,
            );
        }

        $lines = $services->take(6)->map(function (Service $service) {
            $line = '• ' . $service->service_name;
            $line .= $service->speed ? ' — ' . $service->speed : '';
            $line .= ' — ' . $this->peso($service->price) . '/month';
            $line .= $service->installation_fee > 0
                ? ' (installation ' . $this->peso($service->installation_fee) . ')'
                : '';

            return $line;
        })->implode("\n");

        $reply = "Here's what BCTVI offers right now:\n" . $lines;

        if ($services->count() > 6) {
            $reply .= "\n…and " . ($services->count() - 6) . ' more.';
        }

        return $this->answer(
            'plans',
            $reply,
            $client,
            $this->link('Book a plan', 'client.book', 'book'),
        );
    }

    private function coverage(?Client $client): array
    {
        $municipalities = ServiceArea::municipalities();
        $barangays = collect($municipalities)->sum(fn ($m) => count(ServiceArea::barangays($m)));

        $reply = 'BCTVI covers Bantayan Island — ' . $this->list($municipalities)
            . ', in ' . ServiceArea::PROVINCE . ' — ' . $barangays . ' barangays in all. '
            . 'If your barangay is on the list in the booking form, we can reach you.';

        return $this->answer('coverage', $reply, $client);
    }

    private function howToBook(?Client $client): array
    {
        $reply = $client
            ? "Open Bookings, tap Book, then pick the service, the date and a time slot that's free. "
                . "You'll see it as Pending until the office approves it."
            : "Create an account first — it's a short three-step form — then book from your dashboard: "
                . 'pick the service, the date and a free time slot. The office approves it from there.';

        return $this->answer(
            'how_to_book',
            $reply,
            $client,
            $client
                ? $this->link('Book a visit', 'client.book', 'book')
                : $this->link('Create an account', 'register', null),
        );
    }

    private function howToPay(?Client $client): array
    {
        $reply = 'The office records payments as cash, GCash or bank transfer, and issues a receipt against '
            . 'your statement. Settling up is done with the office rather than in the app — '
            . 'what you can see here is what you owe and what has already been receipted.';

        return $this->answer(
            'how_to_pay',
            $reply,
            $client,
            $client ? $this->link('View billing', 'client.billing', 'billing') : null,
        );
    }

    private function reportFault(?Client $client): array
    {
        if (!$client) {
            return $this->answer(
                'report_fault',
                'Sorry about that. Sign in first, then file a report from the Support tab of the BCTVI app — '
                    . 'the team picks those up and you can follow the status there.',
                $client,
                $this->link('Sign in', 'login', null),
            );
        }

        $reply = $this->platform === self::APP
            ? "Sorry about that. File it under Support — give it a subject, describe what's happening and set "
                . 'how urgent it is. It goes straight to the team and you can follow its status in the same place.'
            : 'Sorry about that. Fault reports are filed from the Support tab of the BCTVI app — '
                . "the website doesn't have that screen yet. I can still tell you the status of a report you have already filed.";

        return $this->answer('report_fault', $reply, $client, $this->supportLink());
    }

    private function installationTime(?Client $client): array
    {
        $slots = TimeSlot::available()->pluck('slot_time')
            ->map(fn ($t) => date('g:i A', strtotime($t)))
            ->all();

        $durations = Service::active()->whereNotNull('duration_minutes')->pluck('duration_minutes');

        $reply = $durations->isNotEmpty()
            ? 'A visit is scheduled for about ' . $durations->min()
                . ($durations->min() === $durations->max() ? '' : '–' . $durations->max())
                . ' minutes, depending on the service. '
            : '';

        $reply .= $slots
            ? 'The slots you can pick from are ' . $this->list($slots) . '.'
            : 'The booking form shows which slots are still free on the day you choose.';

        return $this->answer(
            'installation_time',
            $reply,
            $client,
            $this->link('Book a visit', 'client.book', 'book'),
        );
    }

    private function contact(?Client $client): array
    {
        // There is no phone number or opening time recorded anywhere in this
        // system, so the assistant does not have one to give. Saying so and
        // pointing at the channel that does reach a person beats inventing it.
        $reply = "I don't have the office's phone number or opening hours on file. "
            . 'The surest way to reach a person is a support request from the BCTVI app — those go straight '
            . 'to the team and you can follow the reply there.';

        return $this->answer(
            'contact',
            $reply,
            $client,
            $client ? $this->supportLink() : $this->link('Sign in', 'login', null),
        );
    }

    private function help(?Client $client): array
    {
        $reply = $client
            ? "I can check your balance, your next visit, your bookings and your open reports. "
                . 'I can also explain our plans, our coverage, and how booking and payment work. '
                . "I can't change anything on your account — I'll point you at the screen that can."
            : "I can explain our plans, our coverage, how to sign up and how payment works. "
                . 'Sign in and I can also check your balance, your bookings and your reports.';

        return $this->answer('help', $reply, $client);
    }

    private function fallback(?Client $client): array
    {
        $reply = "Sorry — I didn't understand that one. I'm a simple assistant, so I do best with short, "
            . 'direct questions. Try one of these, or file a support request and a person will answer.';

        return $this->answer('fallback', $reply, $client, $client ? $this->supportLink() : null);
    }

    // ─── Shaping a reply ─────────────────────────────────────────────────────

    private function simple(string $intent, string $reply, ?Client $client): array
    {
        return $this->answer($intent, $reply, $client);
    }

    private function signInFirst(string $intent, string $subject): array
    {
        return [
            'reply'       => "You'll need to sign in before I can look up {$subject}.",
            'intent'      => $intent,
            'suggestions' => $this->suggestionsFor(null),
            'link'        => $this->link('Sign in', 'login', null),
        ];
    }

    private function answer(string $intent, string $reply, ?Client $client, ?array $link = null): array
    {
        return [
            'reply'       => $reply,
            'intent'      => $intent,
            'suggestions' => $this->suggestionsFor($client, $intent),
            'link'        => $link,
        ];
    }

    /**
     * The chips offered after an answer.
     *
     * A rule-based assistant is only as good as the questions people think to
     * ask it, so every reply offers three it definitely understands — minus
     * the one just answered.
     */
    private function suggestionsFor(?Client $client, ?string $answered = null): array
    {
        $pool = $client
            ? [
                'balance'          => 'How much do I owe?',
                'next_appointment' => 'When is my next visit?',
                'tickets'          => 'Any updates on my report?',
                'plans'            => 'What plans do you offer?',
                'how_to_pay'       => 'How do I pay?',
                'report_fault'     => 'My internet is slow',
            ]
            : [
                'plans'      => 'What plans do you offer?',
                'coverage'   => 'Do you cover my area?',
                'how_to_book' => 'How do I apply?',
                'how_to_pay' => 'How do I pay?',
            ];

        unset($pool[$answered]);

        return array_slice(array_values($pool), 0, 3);
    }

    /**
     * A pointer at the screen that does the thing.
     *
     * The web reads `url` and the app reads `screen`; each ignores the other's
     * half rather than the server having to know which one is asking.
     */
    /**
     * Support, which only the app has a screen for.
     *
     * On the web there is nothing to link to, so the reply carries no button
     * rather than a dead one.
     */
    private function supportLink(): ?array
    {
        return $this->platform === self::APP
            ? $this->link('Open support', null, 'support')
            : null;
    }

    private function link(string $label, ?string $route, ?string $screen): array
    {
        return [
            'label'  => $label,
            'url'    => $route ? route($route) : null,
            'screen' => $screen,
        ];
    }

    private function peso(float|string|null $amount): string
    {
        return '₱' . number_format((float) $amount, 2);
    }

    /** "a, b and c" */
    private function list(array $items): string
    {
        if (count($items) <= 1) {
            return (string) ($items[0] ?? '');
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' and ' . $last;
    }
}
