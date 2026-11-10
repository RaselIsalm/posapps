<?php

return [
    'thermal' => [
        'header' => ['label' => 'Logo & shop name', 'enabled' => true],
        'seller_identity' => ['label' => 'BIN, phones, website', 'enabled' => true],
        'banner' => ['label' => 'Receipt banner', 'enabled' => true],
        'meta' => ['label' => 'Invoice / customer / payment', 'enabled' => true],
        'items' => ['label' => 'Item list', 'enabled' => true],
        'totals' => ['label' => 'Totals, paid, change', 'enabled' => true],
        'footer' => ['label' => 'Terms & thank you', 'enabled' => true],
    ],
    'a4' => [
        'header' => ['label' => 'Company header & invoice meta', 'enabled' => true],
        'parties' => ['label' => 'Bill to & payment details', 'enabled' => true],
        'items' => ['label' => 'Item table', 'enabled' => true],
        'totals' => ['label' => 'Totals, paid, change, due', 'enabled' => true],
        'terms' => ['label' => 'Terms & conditions', 'enabled' => true],
        'signatures' => ['label' => 'Signatures & footer', 'enabled' => true],
    ],
];
