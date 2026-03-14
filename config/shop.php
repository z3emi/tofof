<?php

return [
    'free_shipping_threshold' => (int) env('SHOP_FREE_SHIPPING_THRESHOLD', 85000),
    'default_shipping_cost' => (float) env('SHOP_DEFAULT_SHIPPING_COST', 5000),
];

