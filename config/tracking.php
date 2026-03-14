<?php

return [
    'api_key' => env('TRACKING_API_KEY', ''),

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('TRACKING_ALLOWED_ORIGINS', '')))),

    'employee_model' => env('TRACKING_EMPLOYEE_MODEL', \App\Models\Manager::class),

    'employee_table' => env('TRACKING_EMPLOYEE_TABLE', 'managers'),
];
