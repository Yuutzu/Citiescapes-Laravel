<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Penalty Schedule (Billing Management System)
    |--------------------------------------------------------------------------
    | Days 1-3: Grace Period (no penalty, just reminders)
    | Day 4 onward: daily penalty applied
    | Day 14: status escalates to Delinquent
    | Day 30: status escalates to Eviction
    */
    'penalty' => [
        'grace_days'      => env('CITIESCAPES_PENALTY_GRACE_DAYS', 3),
        'delinquent_day'  => env('CITIESCAPES_PENALTY_DELINQUENT_DAY', 14),
        'eviction_day'    => env('CITIESCAPES_PENALTY_EVICTION_DAY', 30),
        'default_daily_rate' => 100.00, // PHP
    ],

    /*
    |--------------------------------------------------------------------------
    | User Access & Authentication
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'lockout_threshold' => env('CITIESCAPES_LOCKOUT_THRESHOLD', 5),
        'lockout_minutes'   => env('CITIESCAPES_LOCKOUT_MINUTES', 15),
        'otp_expiry_minutes'=> env('CITIESCAPES_OTP_EXPIRY_MINUTES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contract lifecycle warnings (Days before end_date)
    |--------------------------------------------------------------------------
    */
    'contract' => [
        'warning_days' => [30, 7],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default building configuration (22 rooms across 3 floors)
    |--------------------------------------------------------------------------
    */
    'building' => [
        'name' => 'Citiescapes',
        'address' => 'Remedios St., Bajada, Davao City',
        'floors' => [
            1 => 6,
            2 => 8,
            3 => 8,
        ],
    ],
];
