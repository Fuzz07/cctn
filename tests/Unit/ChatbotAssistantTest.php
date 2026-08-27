<?php

namespace Tests\Unit;

use App\Support\Chatbot\Assistant;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The assistant's intent matcher.
 *
 * This is the half of the assistant that can be wrong quietly: an answer that
 * reads out a balance is either right or obviously broken, but a question
 * routed to the wrong intent just looks like the bot being dim. So every intent
 * has at least one question here, and the pairs that share vocabulary — balance
 * against how_to_pay, my_details against contact — have the case that used to
 * pick the wrong one.
 *
 * It reaches the private matcher by reflection deliberately. Both are internal,
 * neither touches the database, and widening them to public just to test them
 * would put two methods on the class that nothing else should call.
 */
class ChatbotAssistantTest extends TestCase
{
    private Assistant $assistant;
    private \ReflectionMethod $normalise;
    private \ReflectionMethod $match;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assistant = new Assistant();
        $class = new ReflectionClass($this->assistant);

        $this->normalise = $class->getMethod('normalise');
        $this->normalise->setAccessible(true);

        $this->match = $class->getMethod('match');
        $this->match->setAccessible(true);
    }

    private function intentFor(string $question): ?string
    {
        return $this->match->invoke(
            $this->assistant,
            $this->normalise->invoke($this->assistant, $question)
        );
    }

    /**
     * @dataProvider questions
     */
    public function test_it_routes_a_question_to_the_right_intent(string $question, ?string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->intentFor($question),
            'Wrong intent for: ' . $question
        );
    }

    public static function questions(): array
    {
        return [
            // ── Questions about the customer's own record ────────────────────
            ['How much do I owe?',              'balance'],
            ["what's my balance",               'balance'],
            ['do i have any unpaid bills',      'balance'],
            ['magkano ang bayad ko',            'balance'],
            ['pila akong bayronon',             'balance'],
            ['When is my next visit?',          'next_appointment'],
            ['when is my installation',         'next_appointment'],
            ['kanus-a ang installation',        'next_appointment'],
            ['show me my bookings',             'bookings'],
            ['my appointments',                 'bookings'],
            ['any updates on my report?',       'tickets'],
            ['status of my report',             'tickets'],
            ['what is my account number',       'account_number'],
            ['what address do you have for me', 'my_details'],

            // ── Questions about the service ─────────────────────────────────
            ['What plans do you offer?',        'plans'],
            ['how much is the plan',            'plans'],
            ['magkano ang plan',                'plans'],
            ['how fast is your internet',       'plans'],
            ['do you cover santa fe',           'coverage'],
            ['is it available in madridejos',   'coverage'],
            ['How do I apply?',                 'how_to_book'],
            ['i want internet',                 'how_to_book'],
            ['paano mag apply',                 'how_to_book'],
            ['how do i pay',                    'how_to_pay'],
            ['do you accept gcash',             'how_to_pay'],
            ['asa mubayad',                     'how_to_pay'],
            ['my internet is slow',             'report_fault'],
            ['walay internet',                  'report_fault'],
            ['no internet since this morning',  'report_fault'],
            ['how long does installation take', 'installation_time'],
            ['what time slots are available',   'installation_time'],
            ['where is your office',            'contact'],
            ['i want to talk to a person',      'contact'],

            // ── Small talk ──────────────────────────────────────────────────
            ['hello',                           'greeting'],
            ['maayong buntag',                  'greeting'],
            ['salamat',                         'thanks'],
            ['what can you do',                 'help'],

            // ── Nothing it knows: better to say so than to guess ─────────────
            ['do you sell chickens',            null],
            ['zxcvbnm asdfgh',                  null],
            ['the weather is nice today',       null],
        ];
    }

    /**
     * Both of these contain "contact number". The longer phrase belongs to
     * my_details, and phrase scoring is weighted by length so it wins.
     */
    public function test_it_separates_my_details_from_the_office_contact(): void
    {
        $this->assertSame('my_details', $this->intentFor('my contact number'));
        $this->assertSame('contact', $this->intentFor('what is your contact number'));
    }

    /**
     * "pay" is a keyword under how_to_pay and appears in a balance phrase, so
     * these two have to be told apart by the phrase, not the word.
     */
    public function test_it_separates_owing_money_from_how_to_hand_it_over(): void
    {
        $this->assertSame('balance', $this->intentFor('how much do i need to pay'));
        $this->assertSame('how_to_pay', $this->intentFor('where do i pay'));
    }

    public function test_an_empty_message_matches_nothing(): void
    {
        $this->assertNull($this->intentFor(''));
        $this->assertNull($this->intentFor('   ?!  '));
    }

    /** Punctuation and case must not change the answer. */
    public function test_it_normalises_before_matching(): void
    {
        $this->assertSame(
            $this->intentFor('how much do i owe'),
            $this->intentFor('HOW MUCH DO I OWE?!?')
        );
    }
}
