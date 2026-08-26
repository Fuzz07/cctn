<?php

namespace App\Support;

/**
 * The area the network covers: Bantayan Island's three municipalities and
 * their barangays.
 *
 * This list used to be copied into each form that needed it — the walk-in
 * form, the registration wizard and the client profile — which is how the
 * profile form ended up offering Bantayan's barangays to every client
 * regardless of municipality. It lives here now so there is one copy to
 * correct when a barangay is added.
 *
 * The browser gets this same array as JSON (see the address-age partial) and
 * the Android app carries its own copy in core/ServiceArea.kt; all three have
 * to be kept in step.
 */
class ServiceArea
{
    public const PROVINCE = 'Cebu';

    /**
     * Barangays keyed by municipality, each list alphabetical.
     */
    private const BARANGAYS = [
        'Bantayan' => [
            'Atop-atop', 'Baigad', 'Bantigue', 'Baod', 'Binaobao', 'Botigues', 'Doong',
            'Guiwanon', 'Hilotongan', 'Kabac', 'Kabangbang', 'Kampingganon', 'Kangkaibe',
            'Lipayran', 'Luyongbaybay', 'Mojon', 'Obo-ob', 'Patao', 'Puting Bato',
            'Sillion', 'Suba', 'Sulangan', 'Sungko', 'Ticad',
        ],
        'Santa Fe' => [
            'Balidbid', 'Hagdan', 'Hilantagaan', 'Kinatarkan', 'Langub', 'Maricaban',
            'Okoy', 'Poblacion', 'Pooc', 'Talisay',
        ],
        'Madridejos' => [
            'Bunakan', 'Kangwayan', 'Kaongkod', 'Kodia', 'Maalat', 'Malbago', 'Mancilang',
            'Pili', 'Poblacion', 'San Agustin', 'Tabagak', 'Talangnan', 'Tarong', 'Tugas',
        ],
    ];

    /**
     * Every municipality served, in the order the forms list them.
     *
     * @return array<int, string>
     */
    public static function municipalities(): array
    {
        return array_keys(self::BARANGAYS);
    }

    /**
     * The barangays of one municipality, or an empty array if it is not served.
     *
     * @return array<int, string>
     */
    public static function barangays(string $municipality): array
    {
        return self::BARANGAYS[$municipality] ?? [];
    }

    /**
     * The whole map, for handing to the browser as JSON.
     *
     * @return array<string, array<int, string>>
     */
    public static function all(): array
    {
        return self::BARANGAYS;
    }

    /**
     * Whether a barangay actually belongs to the municipality it was sent with.
     * The character-level rules in InputRules cannot catch a mismatched pair.
     */
    public static function isValidPair(?string $municipality, ?string $barangay): bool
    {
        if ($municipality === null || $barangay === null) {
            return false;
        }

        return in_array($barangay, self::barangays($municipality), true);
    }
}
