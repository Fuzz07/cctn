<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Column sorting for the admin list tables.
 *
 * The request carries `sort` (a column key) and `dir` (asc/desc). Both are
 * matched against a whitelist the controller supplies, so a hand-edited URL
 * can never reach an arbitrary column name or direction.
 */
class TableSort
{
    /**
     * Resolve the requested sort against the columns a table allows.
     *
     * @param  array<string, mixed>  $allowed  key => orderable definition
     * @return array{key: string, dir: string, order: mixed}
     */
    public static function resolve(
        Request $request,
        array $allowed,
        string $defaultKey,
        string $defaultDir = 'desc'
    ): array {
        $key = (string) $request->get('sort', '');
        $dir = strtolower((string) $request->get('dir', ''));

        if (! array_key_exists($key, $allowed)) {
            $key = $defaultKey;
        }

        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = $defaultDir;
        }

        return ['key' => $key, 'dir' => $dir, 'order' => $allowed[$key]];
    }

    /**
     * Apply a resolved sort to a query.
     *
     * Each entry in the whitelist is either a column name, a list of column
     * names applied in order, or a closure for cases a plain column cannot
     * express (sorting a booking by the client's surname, say).
     *
     * @param  array{key: string, dir: string, order: mixed}  $sort
     */
    public static function apply($query, array $sort)
    {
        $order = $sort['order'];

        if ($order instanceof \Closure) {
            return $order($query, $sort['dir']);
        }

        foreach ((array) $order as $column) {
            $query->orderBy($column, $sort['dir']);
        }

        return $query;
    }
}
