<?php

namespace App\Support\Chatbot;

use App\Models\Appointment;
use App\Models\BillingAccount;
use App\Models\Client;
use App\Models\MaintenanceRequest;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Support\ServiceArea;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The assistant behind the chat bubble on the site and the Assistant screen in
 * the mobile app.
 *
 * It uses a hybrid intelligence approach:
 * 1. High-precision rule-based & database queries for ISP customer records
 *    (zero hallucination, real-time database queries).
 * 2. Fuzzy typo-tolerant keyword matching.
 * 3. OpenAI GPT Fallback for open-ended customer queries.
 */
class Assistant
{
    public const WEB = 'web';
    public const APP = 'app';

    /**
     * Which client is asking.
     */
    private string $platform = self::WEB;

    /**
     * Statuses that mean a maintenance report is finished with.
     */
    private const CLOSED_TICKET_STATUSES = ['resolved', 'completed', 'closed', 'cancelled', 'rejected'];

    /**
     * What each intent listens for.
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
                'do you serve', 'abot ba', 'saklaw', 'covered area', 'available area',
            ],
            'keywords' => ['coverage', 'area', 'available', 'barangay', 'sakop'],
        ],
        'how_to_book' => [
            'phrases' => [
                'how do i book', 'how to book', 'how do i apply', 'how to apply', 'how do i subscribe',
                'i want to apply', 'i want internet', 'paunsa mag apply', 'paano mag apply',
                'request installation', 'apply for internet',
            ],
            'keywords' => ['apply', 'book', 'subscribe'],
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
            'keywords' => ['slow', 'down', 'offline', 'disconnected', 'problem', 'hinay', 'guba', 'lag'],
        ],
        'installation_time' => [
            'phrases' => [
                'how long does installation take', 'how long is the installation',
                'how long will it take', 'duration of installation', 'what time slots',
                'available times', 'unsa ka dugay',
            ],
            'keywords' => ['slots', 'timeslot'],
        ],
        'location' => [
            'phrases' => [
                'where is your office', 'where are you located', 'office location', 'office address',
                'physical office', 'store location', 'shop location', 'where is bctvi', 'where can i find you',
                'asa inyong office', 'asa ang opisina', 'asa dapit ang office', 'asa mo dapit', 'asa inyong opisina',
                'saan ang office', 'saan kayo located', 'saan ang opisina nyo', 'saan matatagpuan ang office',
                'saan kayo banda', 'branch location', 'our location', 'your location',
            ],
            'keywords' => ['location', 'address', 'office', 'opisina', 'lokasyon', 'dapit', 'branch', 'map', 'maps'],
        ],
        'contact' => [
            'phrases' => [
                'contact number', 'phone number', 'hotline', 'office hours', 'opening hours',
                'business hours', 'operating hours', 'talk to a person', 'talk to someone',
                'speak to staff', 'customer service', 'telephone number', 'cellphone number',
                'unsa inyong contact number', 'anong contact number', 'oras ng opisina',
            ],
            'keywords' => ['contact', 'hotline', 'phone', 'telephone', 'cellphone', 'hours'],
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
     * @return array{reply:string,intent:string,suggestions:array<int,string>,link:?array,plan_cards?:array,coverage_checker?:bool}
     */
    public function respond(?string $message, ?Client $client, string $platform = self::WEB): array
    {
        $this->platform = $platform === self::APP ? self::APP : self::WEB;

        $raw = trim((string) $message);
        $text = $this->normalise($raw);

        if ($text === '') {
            return $this->opening($client, $this->platform);
        }

        $matched = $this->match($text);

        return match ($matched) {
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
            'location'          => $this->location($client),
            'contact'           => $this->contact($client),
            'greeting'          => $this->opening($client),
            'thanks'            => $this->simple('thanks', 'Anytime! Anything else I can check for you?', $client),
            'bye'               => $this->simple('bye', 'Thanks for dropping by. The chat is here whenever you need it.', $client),
            'help'              => $this->help($client),
            default             => $this->fallback($client, $raw),
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
                : "Hi! I'm the BCTVI AI assistant. I can tell you about our plans, coverage, and how to apply. Sign in and I can also check your balance and bookings.",
            'intent'      => 'greeting',
            'suggestions' => $this->suggestionsFor($client),
            'link'        => null,
        ];
    }

    // ─── Matching ────────────────────────────────────────────────────────────

    private function normalise(string $message): string
    {
        $message = mb_strtolower(trim($message));
        $message = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $message) ?? '';

        return trim(preg_replace('/\s+/', ' ', $message) ?? '');
    }

    private function match(string $text): ?string
    {
        $best = null;
        $bestScore = 0;
        $words = explode(' ', $text);

        foreach (self::INTENTS as $intent => $triggers) {
            $score = 0;

            foreach ($triggers['phrases'] as $phrase) {
                if (str_contains($text, $phrase)) {
                    $score += 4 * count(explode(' ', $phrase));
                }
            }

            foreach ($triggers['keywords'] as $keyword) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '(s|es)?\b/u', $text) === 1) {
                    $score += 1;
                    continue;
                }

                // Fuzzy typo tolerance for longer keywords
                $kLen = mb_strlen($keyword);
                if ($kLen >= 4) {
                    foreach ($words as $w) {
                        if (abs(mb_strlen($w) - $kLen) <= 2 && levenshtein($w, $keyword) <= ($kLen >= 6 ? 2 : 1)) {
                            $score += 1;
                            break;
                        }
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $intent;
            }
        }

        return $bestScore > 0 ? $best : null;
    }

    // ─── OpenAI Fallback ─────────────────────────────────────────────────────

    private function askOpenAi(string $message, ?Client $client): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey || !config('services.openai.enabled', true)) {
            return null;
        }

        try {
            $systemPrompt = "You are the official AI Chat Assistant for BCTVI Bantayan (Bantayan Cable & Telecommunications Vision Inc.), the fiber internet and cable TV provider in Bantayan Island, Cebu, Philippines.\n\n"
                . "Key Company Details:\n"
                . "- Service Area: Bantayan Island (covering all 49 barangays across Bantayan, Santa Fe, and Madridejos municipalities).\n"
                . "- Main Office: Poblacion, Bantayan, Cebu.\n"
                . "- Office Hours: Monday to Saturday, 8:00 AM – 5:00 PM (Closed on Sundays).\n"
                . "- Customer Support Hotline: 0999 998 8209.\n"
                . "- Payment Options: GCash online, or over-the-counter walk-in at our Poblacion office.\n"
                . "- Internet Packages: High-speed fiber internet plans starting at affordable monthly rates (e.g., 25Mbps, 50Mbps, 75Mbps, 100Mbps).\n"
                . "- Guidelines:\n"
                . "  1. Keep answers concise, helpful, and friendly (1-3 sentences maximum).\n"
                . "  2. You can understand and reply in English, Tagalog, or Cebuano (Bisaya) depending on what the customer used.\n"
                . "  3. If asked about personal balance, specific invoices, or private ticket updates and customer is not signed in, politely remind them to sign in to their account.";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(6)->post('https://api.openai.com/v1/chat/completions', [
                'model'       => config('services.openai.model', 'gpt-4o-mini'),
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens'  => 250,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if ($content) {
                    return trim($content);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI Chatbot fallback error: ' . $e->getMessage());
        }

        return null;
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
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        if (!$next) {
            return $this->answer(
                'next_appointment',
                "You don't have any upcoming appointments scheduled right now.",
                $client,
                $this->link('Book a visit', 'client.book', 'book'),
            );
        }

        $service = $next->service?->service_name ?? 'service visit';
        $date = $next->appointment_date ? date('M j, Y', strtotime((string) $next->appointment_date)) : 'soon';
        $time = $next->appointment_time ? date('g:i A', strtotime((string) $next->appointment_time)) : '';

        $reply = "Your next scheduled visit is for {$service} on {$date}" . ($time ? " at {$time}" : '') . ". Status: " . ucfirst($next->status) . '.';

        return $this->answer(
            'next_appointment',
            $reply,
            $client,
            $this->link('View appointment', 'client.appointments', 'appointments'),
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
        $services = Service::active()->orderBy('price')->get()->unique('service_name')->values();

        if ($services->isEmpty()) {
            return $this->answer(
                'plans',
                'There are no plans listed at the moment. The office can tell you what is currently on offer.',
                $client,
            );
        }

        $lines = $services->take(6)->map(function (Service $service) {
            $line = '• ' . $service->service_name;
            $line .= $service->speed ? ' (' . $service->speed . ')' : '';
            $line .= ' — ' . $this->peso($service->price) . '/mo';
            $line .= $service->installation_fee > 0
                ? ' + ' . $this->peso($service->installation_fee) . ' install'
                : ' (Free install)';

            return $line;
        })->implode("\n");

        $reply = "Here are our current Fiber Internet plans:\n" . $lines;

        $planCards = $services->take(6)->map(fn ($s) => [
            'id'    => $s->id,
            'name'  => $s->service_name,
            'speed' => $s->speed ?? 'High-Speed Fiber',
            'price' => $this->peso($s->price),
            'fee'   => $s->installation_fee > 0 ? $this->peso($s->installation_fee) : 'Free',
            'book_url' => $client ? route('client.book') : route('register'),
        ])->values()->all();

        return $this->answer(
            'plans',
            $reply,
            $client,
            $this->link('Book a plan', $client ? 'client.book' : 'register', 'book'),
            ['plan_cards' => $planCards]
        );
    }

    private function coverage(?Client $client): array
    {
        $municipalities = ServiceArea::municipalities();
        $barangays = collect($municipalities)->sum(fn ($m) => count(ServiceArea::barangays($m)));

        $reply = 'BCTVI Fiber covers Bantayan Island — ' . $this->list($municipalities)
            . ', in ' . ServiceArea::PROVINCE . ' (' . $barangays . ' barangays in total). '
            . 'Select your municipality and barangay below to check availability instantly!';

        return $this->answer(
            'coverage',
            $reply,
            $client,
            null,
            [
                'coverage_checker' => true,
                'municipalities'   => $municipalities,
                'areas'            => ServiceArea::all(),
            ]
        );
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
        $reply = "You can pay via GCash, bank transfer, or over-the-counter at the BCTVI office in Poblacion. "
            . "Once you submit your payment with the reference number and receipt, the office verifies it.";

        return $this->answer(
            'how_to_pay',
            $reply,
            $client,
            $client ? $this->link('Pay now', 'client.billing', 'billing') : null,
        );
    }

    private function reportFault(?Client $client): array
    {
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

    private function location(?Client $client): array
    {
        $reply = "📍 BCTVI Office Location & Service Area:\n\n"
            . "• Office Address: Poblacion, Bantayan, Cebu (Bantayan Island)\n"
            . "• Coverage: Entire Bantayan Island — Bantayan, Madridejos, and Santa Fe (all 49 barangays)\n"
            . "• Office Hours: Monday – Saturday, 8:00 AM – 5:00 PM (Closed on Sundays)\n"
            . "• Customer Hotline: 0999 998 8209\n\n"
            . "You can visit our main office for payments, applications, and support, or apply and book visits right here online!";

        return $this->answer(
            'location',
            $reply,
            $client,
            $client ? $this->link('Book a visit', 'client.book', 'book') : $this->link('View plans', 'home', null),
        );
    }

    private function contact(?Client $client): array
    {
        $reply = "📞 BCTVI Customer Support & Office Details:\n\n"
            . "• Customer Helpline: 0999 998 8209\n"
            . "• Office Location: Poblacion, Bantayan, Cebu (Bantayan Island)\n"
            . "• Office Hours: Monday – Saturday, 8:00 AM – 5:00 PM\n"
            . "• 24/7 Digital Assistant: Available anytime on Web & Mobile App\n\n"
            . ($this->platform === self::APP
                ? "For connection issues or technical problems, you can also file a ticket directly under the Support tab."
                : "For technical assistance or installation inquiries, feel free to contact us or sign in to book your service online.");

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
                . 'I can also explain our plans, office location, coverage, and how booking and payment work. '
                . "I can't change anything on your account — I'll point you at the screen that can."
            : "I can explain our plans, office location, coverage, how to sign up and how payment works. "
                . 'Sign in and I can also check your balance, your bookings and your reports.';

        return $this->answer('help', $reply, $client);
    }

    private function fallback(?Client $client, string $raw = ''): array
    {
        if ($raw !== '') {
            $aiReply = $this->askOpenAi($raw, $client);
            if ($aiReply) {
                return $this->answer(
                    'ai_response',
                    $aiReply,
                    $client,
                    $client ? $this->supportLink() : $this->link('View plans', 'home', null),
                    ['source' => 'openai']
                );
            }
        }

        $reply = "Sorry — I didn't understand that one. I do best with short, direct questions about plans, coverage, balance, or office hours. "
            . "Try one of the suggestions below, or call our customer hotline at 0999 998 8209.";

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

    private function answer(string $intent, string $reply, ?Client $client, ?array $link = null, array $extra = []): array
    {
        return array_merge([
            'reply'       => $reply,
            'intent'      => $intent,
            'source'      => $extra['source'] ?? 'database',
            'suggestions' => $this->suggestionsFor($client, $intent),
            'link'        => $link,
        ], $extra);
    }

    private function suggestionsFor(?Client $client, ?string $answered = null): array
    {
        $pool = $client
            ? [
                'balance'          => 'How much do I owe?',
                'location'         => 'Where is your office?',
                'next_appointment' => 'When is my next visit?',
                'tickets'          => 'Any updates on my report?',
                'plans'            => 'What plans do you offer?',
                'how_to_pay'       => 'How do I pay?',
                'report_fault'     => 'My internet is slow',
                'contact'          => 'What is your contact number?',
            ]
            : [
                'location'    => 'Where is your office located?',
                'plans'       => 'What plans do you offer?',
                'coverage'    => 'Do you cover my area?',
                'how_to_book' => 'How do I apply?',
                'how_to_pay'  => 'How do I pay?',
                'contact'     => 'What is your contact number?',
            ];

        unset($pool[$answered]);

        return array_slice(array_values($pool), 0, 4);
    }

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

    private function list(array $items): string
    {
        if (count($items) <= 1) {
            return (string) ($items[0] ?? '');
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' and ' . $last;
    }
}
