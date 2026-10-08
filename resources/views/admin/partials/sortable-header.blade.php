@php
    $isActive = ($sort['key'] ?? null) === $key;
    // First click sorts the way that column is usually read; after that it toggles.
    $firstDir = $firstDir ?? 'asc';
    $nextDir = $isActive && $sort['dir'] === $firstDir
        ? ($firstDir === 'asc' ? 'desc' : 'asc')
        : $firstDir;
@endphp
<a class="th-sort {{ $isActive ? 'is-active' : '' }}"
   href="{{ request()->fullUrlWithQuery(['sort' => $key, 'dir' => $nextDir, 'page' => null, 'manage_id' => null]) }}"
   aria-sort="{{ $isActive ? ($sort['dir'] === 'asc' ? 'ascending' : 'descending') : 'none' }}"
   title="Sort by {{ $label }}">
    <span>{{ $label }}</span>
    <span class="th-sort__icon" aria-hidden="true">
        @if ($isActive)
            {!! $sort['dir'] === 'asc' ? '&uarr;' : '&darr;' !!}
        @else
            &updownarrow;
        @endif
    </span>
</a>
