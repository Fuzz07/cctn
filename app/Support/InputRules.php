<?php

namespace App\Support;

/**
 * Field-level input rules, shared by every entry point.
 *
 * The browser-side filter in public/assets/js/form-restrictions.js enforces the
 * same character sets as the user types, but that layer is trivially bypassed
 * (devtools, curl, a stale page, the mobile app), so these rules are the ones
 * that actually protect the database. Keep the two in step: each method below
 * names its counterpart rule in the JS module.
 */
class InputRules
{
    // Every pattern is anchored \A...\z rather than ^...$: in PCRE a trailing
    // '$' also matches just before a final newline, so /^\d+$/ would happily
    // accept "123\n". The character classes use a literal space instead of \s
    // for the same reason — \s would let newlines and tabs through.

    /** Letters, spaces, hyphens, apostrophes — JS rule: "name". */
    public const NAME_REGEX = '/\A[\p{L} \'\-]+\z/u';

    /** Digits only — JS rule: "number". */
    public const NUMBER_REGEX = '/\A\d+\z/';

    /** Philippine mobile, 11 digits starting 09 — JS rule: "mobile". */
    public const MOBILE_REGEX = '/\A09[0-9]{9}\z/';

    /** Letters, digits, spaces and address punctuation — JS rule: "address". */
    public const ADDRESS_REGEX = '/\A[\p{L}\d .,\'\-\/]+\z/u';

    /** Letters, digits and hyphens — JS rule: "idnumber". */
    public const ID_NUMBER_REGEX = '/\A[A-Za-z0-9\-]+\z/';

    /**
     * A person or place name: letters only.
     */
    public static function name(bool $required = true, int $max = 50): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable', 'string', 'max:' . $max],
            ['regex:' . self::NAME_REGEX]
        );
    }

    /**
     * A free-text address line. Digits are allowed because house, block and
     * lot numbers are part of a real address.
     */
    public static function address(bool $required = true, int $max = 255): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable', 'string', 'max:' . $max],
            ['regex:' . self::ADDRESS_REGEX]
        );
    }

    /**
     * A plain numeric string, optionally bounded. Kept as a string rule so
     * leading zeros and long ID numbers survive intact.
     */
    public static function number(bool $required = true, ?int $min = null, ?int $max = null): array
    {
        $rules = [$required ? 'required' : 'nullable', 'regex:' . self::NUMBER_REGEX];

        // 'numeric' makes min/max compare the value rather than its length.
        if ($min !== null || $max !== null) {
            $rules[] = 'numeric';
            if ($min !== null) $rules[] = 'min:' . $min;
            if ($max !== null) $rules[] = 'max:' . $max;
        } else {
            $rules[] = 'string';
        }

        return $rules;
    }

    /**
     * An 11-digit Philippine mobile number.
     */
    public static function mobile(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'digits:11', 'regex:' . self::MOBILE_REGEX];
    }

    /**
     * A government ID number: letters, digits and hyphens, as printed on PH
     * IDs (driver's licence N01-12-345678, SSS 07-1234567-8).
     */
    public static function idNumber(bool $required = true, int $max = 50): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable', 'string', 'max:' . $max],
            ['regex:' . self::ID_NUMBER_REGEX]
        );
    }

    /**
     * Validation messages for the rules above.
     *
     * Pass the field names in each category and get back the messages array
     * for $request->validate(), worded to match the inline messages the user
     * already saw in the browser.
     *
     * @param array $fields ['name' => [...], 'number' => [...], 'address' => [...], ...]
     */
    public static function messages(array $fields = []): array
    {
        $labels = [
            'name'     => 'Please enter letters only.',
            'number'   => 'Please enter numbers only.',
            'address'  => 'Please enter a valid address.',
            'idnumber' => 'Please enter a valid ID number.',
            'mobile'   => 'Mobile number must be exactly 11 digits in the Philippine format (e.g. 09171234567).',
        ];

        $messages = [];

        foreach ($fields as $type => $names) {
            $text = $labels[$type] ?? null;
            if ($text === null) continue;

            foreach ((array) $names as $field) {
                $messages[$field . '.regex'] = $text;

                if ($type === 'mobile') {
                    $messages[$field . '.digits'] = $text;
                }
            }
        }

        return $messages;
    }
}
