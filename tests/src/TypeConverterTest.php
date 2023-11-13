<?php

declare(strict_types=1);

namespace DanBettles\Morf\Tests;

use DanBettles\Morf\Exception\TypeConversionFailedException;
use DanBettles\Morf\TypeConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function array_merge;

use const false;
use const null;
use const true;

class TypeConverterTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $converter = new TypeConverter();

        $this->assertInstanceOf(TypeConverter::class, $converter);
    }

    /** @return array<mixed[]> */
    public function providesConvertedValues(): array
    {
        return [
            [
                true,
                '1',
                'bool',
            ],
            [
                false,
                '0',
                'bool',
            ],
            [
                true,
                '1',
                'boolean',
            ],
            [
                false,
                '0',
                'boolean',
            ],
            [
                123,
                '123',
                'int',
            ],
            [
                123,
                '123',
                'integer',
            ],
            [
                3.142,
                '3.142',
                'float',
            ],
            [
                3.142,
                '3.142',
                'double',
            ],
            [
                'Hello, World!',
                'Hello, World!',
                'string',
            ],
            [
                [1, 2],  // Completely untouched
                [1, 2],
                'array',
            ],
            [
                ['1', '2'],  // Completely untouched
                ['1', '2'],
                'array',
            ],
        ];
    }

    /**
     * @dataProvider providesConvertedValues
     * @param string|mixed[] $expectedConvertedValue
     * @param string|mixed[] $incomingValue
     */
    public function testConvertConvertsAnIncomingValueToTheSpecifiedType(
        $expectedConvertedValue,
        $incomingValue,
        string $typeName
    ): void {
        $converter = new TypeConverter();

        $this->assertSame(
            $expectedConvertedValue,
            $converter->convert($incomingValue, $typeName)
        );
    }

    /** @return array<mixed[]> */
    public function providesInvalidIncomingValues(): array
    {
        return [
            [ true ],
            [ false ],
            [ 123 ],
            [ 3.142 ],
            [ new stdClass() ],
            [ null ],
        ];
    }

    /**
     * @dataProvider providesInvalidIncomingValues
     * @param mixed $invalidIncomingValue
     */
    public function testConvertThrowsAnExceptionIfTheIncomingValueIsNeitherAStringNorAnArray($invalidIncomingValue): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The incoming value is neither a string nor an array');

        (new TypeConverter())->convert($invalidIncomingValue, 'anything');
    }

    /** @return array<mixed[]> */
    public function providesInvalidTypeNames(): array
    {
        return [
            [
                null,  // Normalized type-name
                false,  // Can we convert to this type?
                'BOOL',
            ],
            [
                null,
                false,
                'BOOLEAN',
            ],
            [
                null,
                false,
                'INT',
            ],
            [
                null,
                false,
                'INTEGER',
            ],
            [
                null,
                false,
                'FLOAT',
            ],
            [
                null,
                false,
                'DOUBLE',
            ],
            [
                null,
                false,
                'STRING',
            ],
            [
                null,
                false,
                'ARRAY',
            ],
            [
                null,
                false,
                '',
            ],
            [
                null,
                false,
                'something',
            ],
        ];
    }

    /** @return array<mixed[]> */
    public function providesValidTypeNames(): array
    {
        return [
            [
                'boolean',  // Normalized type-name
                true,  // Can we convert to this type?
                'boolean',
            ],
            [
                'boolean',
                true,
                'bool',
            ],
            [
                'integer',
                true,
                'integer',
            ],
            [
                'integer',
                true,
                'int',
            ],
            [
                'double',
                true,
                'double',
            ],
            [
                'double',
                true,
                'float',
            ],
            [
                'string',
                true,
                'string',
            ],
            [
                'array',
                true,
                'array',
            ],
        ];
    }

    /** @dataProvider providesInvalidTypeNames */
    public function testConvertThrowsAnExceptionIfTheTypeNameIsInvalid(
        ?string $normalizedTypeName,
        bool $canBeConvertedTo,
        string $invalidTypeName
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type-name is invalid');

        (new TypeConverter())->convert('anything', $invalidTypeName);
    }

    /** @return array<mixed[]> */
    public function providesInvalidTypeNamesForAnArrayValue(): array
    {
        return [
            [
                'Failed to convert the variable, [array], to a boolean',
                'bool',
            ],
            [
                'Failed to convert the variable, [array], to a boolean',
                'boolean',
            ],
            [
                'Failed to convert the variable, [array], to an integer',
                'int',
            ],
            [
                'Failed to convert the variable, [array], to an integer',
                'integer',
            ],
            [
                'Failed to convert the variable, [array], to a double',
                'float',
            ],
            [
                'Failed to convert the variable, [array], to a double',
                'double',
            ],
            [
                'Failed to convert the variable, [array], to a string',
                'string',
            ],
        ];
    }

    /** @dataProvider providesInvalidTypeNamesForAnArrayValue */
    public function testConvertThrowsAnExceptionIfAnArrayIsPassedButTheTypeNameIsInappropriate(
        string $expectedExceptionMessage,
        string $typeName
    ): void {
        $this->expectException(TypeConversionFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        (new TypeConverter())->convert([], $typeName);
    }

    /** @return array<mixed[]> */
    public function providesValuesThatCannotBeConverted(): array
    {
        return [
            // ###> Not Booleans ###
            [
                'Failed to convert the variable, `"2"`, to a boolean',
                '2',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"foo"`, to a boolean',
                'foo',
                'boolean',
            ],
            [
                'Failed to convert the variable, `""`, to a boolean',
                '',  // Ambiguous
                'boolean',
            ],
            [
                'Failed to convert the variable, `"true"`, to a boolean',
                'true',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"false"`, to a boolean',
                'false',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"on"`, to a boolean',
                'on',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"off"`, to a boolean',
                'off',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"yes"`, to a boolean',
                'yes',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"no"`, to a boolean',
                'no',
                'boolean',
            ],
            // ###< Not Booleans ###

            // ###> Not ints ###
            [
                'Failed to convert the variable, `"--1"`, to an integer',
                '--1',
                'integer',
            ],
            [
                'Failed to convert the variable, `"1.0"`, to an integer',
                '1.0',
                'integer',
            ],
            [
                'Failed to convert the variable, `"Hello, World!"`, to an integer',
                'Hello, World!',
                'integer',
            ],
            [
                'Failed to convert the variable, `""`, to an integer',
                '',  // We can't know for sure what blank means in this case
                'integer',
            ],
            // ###< Not ints ###

            // ###> Not floats ###
            [
                'Failed to convert the variable, `"1.0.0"`, to a double',
                '1.0.0',
                'double',
            ],
            [
                'Failed to convert the variable, `"Hello, World!"`, to a double',
                'Hello, World!',
                'double',
            ],
            [
                'Failed to convert the variable, `""`, to a double',
                '',  // We can't know for sure what blank means in this case
                'double',
            ],
            // ###< Not floats ###

            // ###> Not arrays ###
            [
                'Failed to convert the variable, `"foo"`, to an array',
                'foo',
                'array',
            ],
            [
                'Failed to convert the variable, `""`, to an array',
                '',  // So WTF is this meant to mean?
                'array',
            ],
            // ###< Not arrays ###
        ];
    }

    /** @dataProvider providesValuesThatCannotBeConverted */
    public function testConvertThrowsAnExceptionIfTheIncomingValueCannotBeConvertedToTheSpecifiedType(
        string $expectedExceptionMessage,
        string $invalidIncomingValue,
        string $typeName
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        (new TypeConverter())->convert($invalidIncomingValue, $typeName);
    }

    /** @return array<mixed[]> */
    public function providesTypeNames(): array
    {
        return array_merge(
            $this->providesValidTypeNames(),
            $this->providesInvalidTypeNames()
        );
    }

    /** @dataProvider providesTypeNames */
    public function testNormalizetypenameNormalizesTheTypeNameOrReturnsNull(
        ?string $normalizedTypeName,
        bool $canBeConvertedTo,
        string $typeName
    ): void {
        $this->assertSame(
            $normalizedTypeName,
            (new TypeConverter())->normalizeTypeName($typeName)
        );
    }
}
