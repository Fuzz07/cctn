<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking System Toggle
    |--------------------------------------------------------------------------
    | When false, the online booking form is disabled for clients.
    */
    'booking_enabled' => filter_var(env('BOOKING_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode (custom flag, separate from Laravel's artisan down)
    |--------------------------------------------------------------------------
    | When true, a maintenance banner is shown to clients on the front-end.
    */
    'maintenance_mode' => filter_var(env('MAINTENANCE_MODE', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Max Appointments Per Day
    |--------------------------------------------------------------------------
    */
    'max_daily_bookings' => (int) env('MAX_DAILY_BOOKINGS', 10),
];
