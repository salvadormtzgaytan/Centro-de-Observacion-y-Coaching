<?php
return [
    // Días que dura vigente el link de firma/descarga
    'sign_link_ttl_days' => (int) env('SIGN_LINK_TTL_DAYS', 7),
    'fiscal_year_start_month' =>  (int) env('FISCAL_YEAR_START_MONTH', 1),
    'default_fiscal_year' => (int) env('DEFAULT_FISCAL_YEAR', now()->year),
];
