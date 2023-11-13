#!/usr/bin/env php
<?php

use DanBettles\Morf\Filter;

require_once __DIR__ . '/../vendor/autoload.php';

$definitions = [
    [
        'name' => 'anything',  // N.B. `name` is the only required element
        // 'type' => 'string',  // The default output-type
    ],
    [
        'name' => 'show_retirement',
        'type' => 'bool',  // Or use `"boolean"`
        // 'default' => false,  // The built-in default value
    ],
    [
        'name' => 'show_under_offer',
        'type' => 'bool',
        'default' => true,  // N.B. the same type as named in `type`
    ],
    [
        'name' => 'location_id',
        'type' => 'int',  // Or use `"integer"`
        'default' => 7,
        'validator' => 'positiveInteger',  // The name of a validator method in `DanBettles\Morf\Validators`
    ],
    [
        'name' => 'num_rooms',
        'type' => 'int',
        'default' => 0,  // Indicates "any" in this example
        'validValues' => [0, 1, 2, 3],  // `validValues` will override `validator` if both elements are present
    ],
    [
        'name' => 'a_floating_point_number',
        'type' => 'float',  // Or use `"double"`
    ],
    [
        'name' => 'an_array',
        'type' => 'array',
    ],
];

// Empty request, defaults applied:

$actual = Filter::create($definitions)->filter([]);

$expected = [
    'anything' => '',
    'show_retirement' => false,
    'show_under_offer' => true,
    'location_id' => 7,
    'num_rooms' => 0,
    'a_floating_point_number' => 0.0,
    'an_array' => [],
];

assert($expected === $actual);

// Form submitted, say:

$actual = Filter::create($definitions)->filter([
    'anything' => 'Hello, World!',
    'show_retirement' => '0',
    'show_under_offer' => '0',
    'location_id' => '2',
    'num_rooms' => '2',
    'a_floating_point_number' => '3.142',
    'an_array' => ['foo', 'bar', 'baz'],
]);

$expected = [
    'anything' => 'Hello, World!',
    'show_retirement' => false,
    'show_under_offer' => false,
    'location_id' => 2,
    'num_rooms' => 2,
    'a_floating_point_number' => 3.142,
    'an_array' => ['foo', 'bar', 'baz'],
];

assert($expected === $actual);
