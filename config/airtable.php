<?php

return [
    'api_key' => env('AIRTABLE_API_KEY', ''),
    'base_id' => env('AIRTABLE_BASE_ID', ''),
    'products_table' => env('AIRTABLE_PRODUCTS_TABLE', 'Products'),
    'categories_table' => env('AIRTABLE_CATEGORIES_TABLE', 'Categories'),
    'attributes_table' => env('AIRTABLE_ATTRIBUTES_TABLE', 'Product attributes'),
];
