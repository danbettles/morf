<?php

declare(strict_types=1);

namespace DanBettles\Morf\Tests;

use DanBettles\Morf\Exception\TypeConversionFailedException;
use DanBettles\Morf\Filter;
use DanBettles\Morf\TypeConverter;
use DanBettles\Morf\Validators;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use const false;
use const null;
use const true;

/**
 * @phpstan-import-type CompleteDef from Filter
 * @phpstan-import-type Filtered from Filter
 */
class FilterTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $filter = new Filter(new TypeConverter(), new Validators(), [
            [
                'name' => 'foo',
                'type' => 'string',
            ],
        ]);

        $this->assertSame([
            [
                'name' => 'foo',
                'type' => 'string',
                'default' => '',
            ],
        ], $filter->getDefs());
    }

    public function testCreateReturnsANewFilter(): void
    {
        $filter = Filter::create([
            [
                'name' => 'foo',
                'type' => 'string',
            ],
        ]);

        $this->assertInstanceOf(Filter::class, $filter);

        $this->assertSame([
            [
                'name' => 'foo',
                'type' => 'string',
                'default' => '',
            ],
        ], $filter->getDefs());
    }

    public function testThereIsADefaultOutputType(): void
    {
        $filter = Filter::create([
            [
                'name' => 'foo',
            ],
        ]);

        $this->assertSame([
            [
                'name' => 'foo',
                'type' => 'string',
                'default' => '',
            ],
        ], $filter->getDefs());
    }

    public function testThrowsAnExceptionIfTheArrayOfDefsIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There are no defs');

        Filter::create([]);
    }

    /** @return array<mixed[]> */
    public function providesIncompleteDefs(): array
    {
        return [
            [
                'Definition [0] is missing elements: `name`',
                [
                    [],
                ],
            ],
            [
                'Definition [0] is missing elements: `name`',
                [
                    [
                        'type' => 'int',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesIncompleteDefs
     * @phpstan-param array<CompleteDef> $defs
     */
    public function testThrowsAnExceptionIfADefIsIncomplete(
        string $expectedExceptionMessage,
        array $defs
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Filter::create($defs);
    }

    /** @return array<mixed[]> */
    public function providesDefsContainingADefaultValueWithAnUnacceptableType(): array
    {
        return [
            [
                'Definition [0] is invalid: The type of the default value must be string|null',
                [
                    [
                        'name' => 'a_number',
                        'default' => 123,
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The type of the default value must be string|null',
                [
                    [
                        'name' => 'a_number',
                        'type' => 'string',
                        'default' => 123,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesDefsContainingADefaultValueWithAnUnacceptableType
     * @phpstan-param array<CompleteDef> $defs
     */
    public function testThrowsAnExceptionIfTheTypeOfTheDefaultValueIsUnacceptable(
        string $expectedExceptionMessage,
        array $defs
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Filter::create($defs);
    }

    public function testNormalizesTheNamesOfOutputTypes(): void
    {
        $defs = [
            [
                'name' => 'foo',
                'type' => 'bool',
            ],
            [
                'name' => 'bar',
                'type' => 'int',
            ],
            [
                'name' => 'baz',
                'type' => 'float',
            ],
        ];

        $filter = Filter::create($defs);

        $this->assertSame([
            [
                'name' => 'foo',
                'type' => 'boolean',
                'default' => false,
            ],
            [
                'name' => 'bar',
                'type' => 'integer',
                'default' => 0,
            ],
            [
                'name' => 'baz',
                'type' => 'double',
                'default' => 0.0,
            ],
        ], $filter->getDefs());
    }

    /** @return array<mixed[]> */
    public function providesDefsContainingAnUnsupportedOutputType(): array
    {
        return [
            [
                'Definition [0] is invalid: The output-type `BOOLEAN` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'BOOLEAN',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `BOOL` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'BOOL',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `INTEGER` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'INTEGER',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `INT` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'INT',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `DOUBLE` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'DOUBLE',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `FLOAT` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'FLOAT',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `STRING` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'STRING',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `ARRAY` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'ARRAY',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `object` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'object',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `OBJECT` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'OBJECT',
                    ],
                ],
            ],
            [  // #2
                'Definition [0] is invalid: The output-type `resource` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'resource',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `RESOURCE` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'RESOURCE',
                    ],
                ],
            ],
            [
                'Definition [0] is invalid: The output-type `null` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'null',
                    ],
                ],
            ],
            [  // #5
                'Definition [0] is invalid: The output-type `NULL` is not supported',
                [
                    [
                        'name' => 'foo',
                        'type' => 'NULL',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesDefsContainingAnUnsupportedOutputType
     * @phpstan-param array<CompleteDef> $defs
     */
    public function testThrowsAnExceptionIfTheNameOfAnOutputTypeIsNotSupported(
        string $expectedExceptionMessage,
        array $defs
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Filter::create($defs);
    }

    public function testSetsDefaultValuesToSomethingAppropriate(): void
    {
        $filter = Filter::create([
            [
                'name' => 'foo',
                'type' => 'bool',
            ],
            [
                'name' => 'bar',
                'type' => 'boolean',
            ],
            [
                'name' => 'baz',
                'type' => 'int',
            ],
            [
                'name' => 'qux',
                'type' => 'integer',
            ],
            [
                'name' => 'quux',
                'type' => 'float',
            ],
            [
                'name' => 'corge',
                'type' => 'double',
            ],
            [
                'name' => 'grault',
                'type' => 'string',
            ],
            [
                'name' => 'garply',
                'type' => 'array',
            ],
        ]);

        $this->assertSame([
            [
                'name' => 'foo',
                'type' => 'boolean',
                'default' => false,
            ],
            [
                'name' => 'bar',
                'type' => 'boolean',
                'default' => false,
            ],
            [
                'name' => 'baz',
                'type' => 'integer',
                'default' => 0,
            ],
            [
                'name' => 'qux',
                'type' => 'integer',
                'default' => 0,
            ],
            [
                'name' => 'quux',
                'type' => 'double',
                'default' => 0.0,
            ],
            [
                'name' => 'corge',
                'type' => 'double',
                'default' => 0.0,
            ],
            [
                'name' => 'grault',
                'type' => 'string',
                'default' => '',
            ],
            [
                'name' => 'garply',
                'type' => 'array',
                'default' => [],
            ],
        ], $filter->getDefs());
    }

    /** @return array<mixed[]> */
    public function providesFilteredValues(): array
    {
        return [
            // #0 Valid value; converted to a Boolean
            [
                [
                    'foo' => true,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'bool',
                    ],
                ],
                [
                    'foo' => '1',
                ],
            ],
            // #1 Valid value; converted to a Boolean
            [
                [
                    'foo' => false,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'bool',
                    ],
                ],
                [
                    'foo' => '0',
                ],
            ],
            // #2 Valid value; converted to an integer
            [
                [
                    'foo' => 123,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                    ],
                ],
                [
                    'foo' => '123',
                ],
            ],
            // #3 Valid value; converted to a float
            [
                [
                    'foo' => 1.23,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'float',
                    ],
                ],
                [
                    'foo' => '1.23',
                ],
            ],
            // #4 Valid string value
            [
                [
                    'foo' => 'Hello, World!',
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'string',
                    ],
                ],
                [
                    'foo' => 'Hello, World!',
                ],
            ],
            // #5 Valid array
            [
                [
                    'foo' => ['foo', 'bar'],
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'array',
                    ],
                ],
                [
                    'foo' => ['foo', 'bar'],
                ],
            ],

            // #6 The default output-type is `string`
            [
                [
                    'foo' => 'Hello, World!',
                ],
                [
                    [
                        'name' => 'foo',
                    ],
                ],
                [
                    'foo' => 'Hello, World!',
                ],
            ],

            // #7 Boolean parameter missing; built-in default-value used
            [
                [
                    'foo' => false,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'bool',
                    ],
                ],
                [],
            ],
            // #8 Integer parameter missing; built-in default-value used
            [
                [
                    'foo' => 0,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                    ],
                ],
                [],
            ],
            // #9 Float parameter missing; built-in default-value used
            [
                [
                    'foo' => 0.00,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'float',
                    ],
                ],
                [],
            ],
            // #10 String parameter missing; built-in default-value used
            [
                [
                    'foo' => '',
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'string',
                    ],
                ],
                [],
            ],
            // #11 Array parameter missing; built-in default-value used
            [
                [
                    'foo' => [],
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'array',
                    ],
                ],
                [],
            ],

            // #12 Boolean parameter missing; user default-value returned
            [
                [
                    'foo' => true,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'bool',
                        'default' => true,
                    ],
                ],
                [],
            ],
            // #13 Integer parameter missing; user default-value returned
            [
                [
                    'foo' => 999,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'default' => 999,
                    ],
                ],
                [],
            ],
            // #14 Float parameter missing; user default-value returned
            [
                [
                    'foo' => 3.21,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'float',
                        'default' => 3.21,
                    ],
                ],
                [],
            ],
            // #15 String parameter missing; user default-value returned
            [
                [
                    'foo' => 'nothing',
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'string',
                        'default' => 'nothing',
                    ],
                ],
                [],
            ],
            // #16 Array parameter missing; user default-value returned
            [
                [
                    'foo' => ['something'],
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'array',
                        'default' => ['something'],
                    ],
                ],
                [],
            ],

            // #17 `null` can be used as a default value for any output type
            [
                [
                    'foo' => null,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'bool',
                        'default' => null,
                    ],
                ],
                [],
            ],
            // #18 `null` can be used as a default value for any output type
            [
                [
                    'foo' => null,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'default' => null,
                    ],
                ],
                [],
            ],
            // #19 `null` can be used as a default value for any output type
            [
                [
                    'foo' => null,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'float',
                        'default' => null,
                    ],
                ],
                [],
            ],
            // #20 `null` can be used as a default value for any output type
            [
                [
                    'foo' => null,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'string',
                        'default' => null,
                    ],
                ],
                [],
            ],
            // #21 `null` can be used as a default value for any output type
            [
                [
                    'foo' => null,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'array',
                        'default' => null,
                    ],
                ],
                [],
            ],

            // #22 Value IN list; def without user default-value; value correct type
            [
                [
                    'foo' => 2,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'validValues' => [1, 2, 4],
                    ],
                ],
                [
                    'foo' => '2',
                ],
            ],
            // #23 Value IN list; def without user default-value; value correct type
            [
                [
                    'direction' => 'south',
                ],
                [
                    [
                        'name' => 'direction',
                        'validValues' => ['north', 'east', 'south', 'west'],
                    ],
                ],
                [
                    'direction' => 'south',
                ],
            ],
            // #24 **For now**, `validValues` is ignored when `type` is "array"
            [
                [
                    'array_of_something' => ['baz', 'qux'],
                ],
                [
                    [
                        'name' => 'array_of_something',
                        'type' => 'array',
                        'validValues' => ['foo', 'bar',],
                    ],
                ],
                [
                    'array_of_something' => ['baz', 'qux'],
                ],
            ],
            // #25 Value meets the requirements of a custom validator
            [
                [
                    'foo' => 37,
                ],
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'validator' => fn ($convertedDirtyValue) => 37 === $convertedDirtyValue,
                    ],
                ],
                [
                    'foo' => '37',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesFilteredValues
     * @param Filtered $expectedFilteredValues
     * @phpstan-param array<CompleteDef> $defs
     * @param mixed[] $dirty
     */
    public function testFilterFiltersTheSpecifiedValues(
        array $expectedFilteredValues,
        array $defs,
        array $dirty
    ): void {
        $this->assertSame(
            $expectedFilteredValues,
            Filter::create($defs)->filter($dirty)
        );
    }

    /** @return array<mixed[]> */
    public function providesValuesThatCannotBeConvertedToTheOutputType(): array
    {
        return [
            // ###> A blank is valid only in the case of strings ###
            // #0 Value not in list; def with user default-value
            [
                'Failed to convert the variable, `""`, to an integer',
                [
                    [
                        'name' => 'blank_for_int',
                        'type' => 'int',
                        'default' => 0,
                        'validValues' => [0, 1, 2],
                    ],
                ],
                [
                    'blank_for_int' => '',
                ],
            ],
            // #1 Value not in list; def with user default-value
            [
                'Failed to convert the variable, `""`, to a double',
                [
                    [
                        'name' => 'blank_for_float',
                        'type' => 'float',
                        'default' => 0.0,
                        'validValues' => [0.0, 1.1, 2.2],
                    ],
                ],
                [
                    'blank_for_float' => '',
                ],
            ],

            // #2
            [
                'Failed to convert the variable, `""`, to a boolean',
                [
                    [
                        'name' => 'blank_for_bool',
                        'type' => 'bool',
                        'default' => false,
                    ],
                ],
                [
                    'blank_for_bool' => '',
                ],
            ],
            // #3
            [
                'Failed to convert the variable, `""`, to an int',
                [
                    [
                        'name' => 'blank_for_int',
                        'type' => 'int',
                        'default' => 0,
                    ],
                ],
                [
                    'blank_for_int' => '',
                ],
            ],
            // #4
            [
                'Failed to convert the variable, `""`, to a double',
                [
                    [
                        'name' => 'blank_for_double',
                        'type' => 'float',
                        'default' => 0.0,
                    ],
                ],
                [
                    'blank_for_double' => '',
                ],
            ],
            // #5
            [
                'Failed to convert the variable, `""`, to an array',
                [
                    [
                        'name' => 'blank_for_array',
                        'type' => 'array',
                        'default' => [],
                    ],
                ],
                [
                    'blank_for_array' => '',
                ],
            ],
            // ###< A blank is valid only in the case of strings ###

            // ###> Only `"1"` and `"0"` are accepted for Booleans ###
            // #6
            [
                'Failed to convert the variable, `"true"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => false,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'true',
                ],
            ],
            // #7
            [
                'Failed to convert the variable, `"false"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => true,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'false',
                ],
            ],
            // #8
            [
                'Failed to convert the variable, `"on"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => false,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'on',
                ],
            ],
            // #9
            [
                'Failed to convert the variable, `"off"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => true,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'off',
                ],
            ],
            // #10
            [
                'Failed to convert the variable, `"yes"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => false,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'yes',
                ],
            ],
            // #11
            [
                'Failed to convert the variable, `"no"`, to a boolean',
                [
                    [
                        'name' => 'invalid_boolean_value',
                        'type' => 'bool',
                        'default' => true,
                    ],
                ],
                [
                    'invalid_boolean_value' => 'no',
                ],
            ],
            // ###< Only `"1"` and `"0"` are accepted for Booleans ###

            // ###> An array must always be met with an array ###
            // #12
            [
                'Failed to convert the variable, `"123"`, to an array',
                [
                    [
                        'name' => 'some_other_string_for_array',
                        'type' => 'array',
                        'default' => [],
                    ],
                ],
                [
                    'some_other_string_for_array' => '123',
                ],
            ],
            // #13
            [
                'Failed to convert the variable, `"3.142"`, to an array',
                [
                    [
                        'name' => 'some_other_string_for_array',
                        'type' => 'array',
                        'default' => [],
                    ],
                ],
                [
                    'some_other_string_for_array' => '3.142',
                ],
            ],
            // #14
            [
                'Failed to convert the variable, `"foo"`, to an array',
                [
                    [
                        'name' => 'some_other_string_for_array',
                        'type' => 'array',
                        'default' => [],
                    ],
                ],
                [
                    'some_other_string_for_array' => 'foo',
                ],
            ],
            // ###< An array must always be met with an array ###
        ];
    }

    /**
     * @dataProvider providesValuesThatCannotBeConvertedToTheOutputType
     * @phpstan-param array<CompleteDef> $defs
     * @param mixed[] $dirty
     */
    public function testFilterThrowsAnExceptionIfAValueCannotBeConvertedToTheOutputType(
        string $expectedExceptionMessage,
        array $defs,
        array $dirty
    ): void {
        $this->expectException(TypeConversionFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Filter::create($defs)->filter($dirty);
    }

    /** @return array<mixed[]> */
    public function providesInvalidValues(): array
    {
        return [
            // ###> Exception thrown if not in the list ###
            // #0 Value not in list; def without user default-value; value correct type
            [
                'The value of `foo` is invalid',
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'validValues' => [1, 2, 4],
                    ],
                ],
                [
                    'foo' => '5',
                ],
            ],
            // #1 Value not in list; def without user default-value; value correct type
            [
                'The value of `direction` is invalid',
                [
                    [
                        'name' => 'direction',
                        'validValues' => ['north', 'east', 'south', 'west'],
                    ],
                ],
                [
                    'direction' => 'left',
                ],
            ],
            // ###< Exception thrown if not in the list ###

            // ###> Default ignored: exception always thrown ###
            // #2 Value not in list; def WITH user default-value; value correct type
            [
                'The value of `foo` is invalid',
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'validValues' => [1, 2, 4],
                        'default' => -1,
                    ],
                ],
                [
                    'foo' => '5',
                ],
            ],
            // #3 Value not in list; def WITH user default-value; value correct type
            [
                'The value of `direction` is invalid',
                [
                    [
                        'name' => 'direction',
                        'validValues' => ['north', 'east', 'south', 'west'],
                        'default' => 'south',
                    ],
                ],
                [
                    'direction' => 'left',
                ],
            ],
            // ###< Default ignored: exception always thrown ###

            // #4 Value invalid; no default
            [
                'The value of `foo` is invalid',
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'validator' => fn ($convertedDirtyValue) => 37 === $convertedDirtyValue,
                    ],
                ],
                [
                    'foo' => '38',
                ],
            ],
            // #5 Value invalid; default provided
            [
                'The value of `foo` is invalid',
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'default' => -1,
                        'validator' => fn ($convertedDirtyValue) => 37 === $convertedDirtyValue,
                    ],
                ],
                [
                    'foo' => '38',
                ],
            ],
            // #6 `validValues` takes precedence over `validator`
            [
                'The value of `foo` is invalid',
                [
                    [
                        'name' => 'foo',
                        'type' => 'int',
                        'default' => -1,
                        'validator' => fn () => true,
                        'validValues' => [1, 2, 3],
                    ],
                ],
                [
                    'foo' => '0',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providesInvalidValues
     * @phpstan-param array<CompleteDef> $defs
     * @param mixed[] $dirty
     */
    public function testFilterThrowsAnExceptionIfAValueIsInvalid(
        string $expectedExceptionMessage,
        array $defs,
        array $dirty
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Filter::create($defs)->filter($dirty);
    }

    public function testFilterCallsValidatorMethodsInTheValidatorsClass(): void
    {
        $validatorsMock = $this
            ->getMockBuilder(Validators::class)
            ->onlyMethods(['invokeValidator'])
            ->getMock()
        ;

        $validatorsMock
            ->expects($this->once())
            ->method('invokeValidator')
            ->with('isoDate', '2023-10-30')
            ->willReturn(true)
        ;

        /** @var Validators $validatorsMock */

        $filter = new Filter(new TypeConverter(), $validatorsMock, [
            [
                'name' => 'start_date',
                'validator' => 'isoDate',
            ],
        ]);

        $input = [
            'start_date' => '2023-10-30',
        ];

        // The same out as we put in, because the values are valid
        $this->assertSame($input, $filter->filter($input));
    }
}
