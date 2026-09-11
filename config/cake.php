<?php

return [
    'options' => [
        'sizes' => [
            [
                'code' => 'bento',
                'label' => 'Bento (4–6 servings)',
                'price_adjustment_cents' => -20000,
            ],
            [
                'code' => 'half-kg',
                'label' => '0.5 kg (6–8 servings)',
                'price_adjustment_cents' => 0,
            ],
            [
                'code' => 'one-kg',
                'label' => '1 kg (10–12 servings)',
                'price_adjustment_cents' => 18000,
            ],
            [
                'code' => 'one-and-half-kg',
                'label' => '1.5 kg (16–20 servings)',
                'price_adjustment_cents' => 36000,
            ],
            [
                'code' => 'two-kg',
                'label' => '2 kg (24–30 servings)',
                'price_adjustment_cents' => 56000,
            ],
        ],
        'fillings' => [
            [
                'code' => 'none',
                'label' => 'No extra filling',
                'price_adjustment_cents' => 0,
            ],
            [
                'code' => 'strawberry',
                'label' => 'Fresh Strawberry',
                'price_adjustment_cents' => 6000,
            ],
            [
                'code' => 'mango-cream',
                'label' => 'Mango Cream',
                'price_adjustment_cents' => 7000,
            ],
            [
                'code' => 'salted-caramel',
                'label' => 'Salted Caramel',
                'price_adjustment_cents' => 8000,
            ],
        ],
        'frostings' => [
            [
                'code' => 'whipped-cream',
                'label' => 'Whipped Cream',
                'price_adjustment_cents' => 0,
            ],
            [
                'code' => 'buttercream',
                'label' => 'Buttercream',
                'price_adjustment_cents' => 4000,
            ],
            [
                'code' => 'cream-cheese',
                'label' => 'Cream Cheese',
                'price_adjustment_cents' => 7000,
            ],
        ],
        'toppings' => [
            [
                'code' => 'fresh-fruit',
                'label' => 'Fresh Fruit',
                'price_adjustment_cents' => 8000,
            ],
            [
                'code' => 'coconut',
                'label' => 'Toasted Coconut',
                'price_adjustment_cents' => 4000,
            ],
            [
                'code' => 'chocolate-drip',
                'label' => 'Chocolate Drip',
                'price_adjustment_cents' => 5000,
            ],
            [
                'code' => 'macarons',
                'label' => 'Macarons',
                'price_adjustment_cents' => 12000,
            ],
        ],
        'decorations' => [
            [
                'code' => 'minimal',
                'label' => 'Minimal Celebration',
                'price_adjustment_cents' => 0,
            ],
            [
                'code' => 'floral',
                'label' => 'Floral Piping',
                'price_adjustment_cents' => 15000,
            ],
            [
                'code' => 'celebration',
                'label' => 'Celebration Trim',
                'price_adjustment_cents' => 10000,
            ],
        ],
    ],
    'combinations' => [
        'minimal' => 'Minimal cream finish',
        'rosettes' => 'Whipped cream rosettes',
        'coconut' => 'Ube coconut garnish',
        'fruit' => 'Fresh fruit topping',
        'web-glaze' => 'Feathered web glaze',
        'drip' => 'Chocolate drip',
        'berries' => 'Fresh berry topping',
        'macarons' => 'Macaron decoration',
        'floral' => 'Floral celebration decoration',
        'bento' => 'Bento-size presentation',
    ],
];
