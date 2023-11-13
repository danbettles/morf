# Morf

Morf is used to filter request-parameters *en masse*.  It is configured with an array of definitions that describes each parameter you're interested in, and serves-up valid, type-cast values; it'll spit-out an exception when something's overtly wrong.

For safety's sake, Morf is strict and opinionated, and uses PHP built-ins whenever possible.

## Example

It's pretty straightforward, as this example should&mdash;hopefully&mdash;show:

```php
use DanBettles\Morf\Filter;

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
```

## Valid Inputs

| Type              | Valid Input                       |
| ----------------- | --------------------------------- |
| `bool`, `boolean` | `"1"`, `"0"`[^1]                  |
| `int`, `integer`  | Any integer in string form        |
| `float`, `double` | Any float/integer in string form  |
| `string`          | Any string :smile:                |
| `array`           | Any array                         |

[^1]: We take the view that being explicit is better (safer) in the long run.  So, for example, a Boolean parameter with a blank value is treated as invalid because you can easily represent a Boolean value using `"1"` or `"0"`&mdash;which we think is more intuitive in any case.

## Default Values

The built-in default values for each output-type&mdash;the values returned when parameters aren't set&mdash;are the same as those used in [`Symfony\Component\HttpFoundation\ParameterBag`](https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpFoundation/ParameterBag.php), which we think are intuitive.  No problem if you disagree, or if your application requires something different, because you can specify parameter-level default values&mdash;[see the example above](#example).
