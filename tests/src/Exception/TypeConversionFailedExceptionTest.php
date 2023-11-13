<?php

declare(strict_types=1);

namespace DanBettles\Morf\Tests\Exception;

use DanBettles\Morf\Exception\TypeConversionFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class TypeConversionFailedExceptionTest extends TestCase
{
    public function testIsARuntimeexception(): void
    {
        $class = new ReflectionClass(TypeConversionFailedException::class);

        $this->assertTrue($class->isSubclassOf(RuntimeException::class));
    }

    /** @return array<mixed[]> */
    public function providesExceptionMessages(): array
    {
        return [
            [
                'Failed to convert the variable, `"foo"`, to a boolean',
                'foo',
                'boolean',
            ],
            [
                'Failed to convert the variable, `"bar"`, to a bool',
                'bar',
                'bool',
            ],
            [
                'Failed to convert the variable, `"baz"`, to an integer',
                'baz',
                'integer',
            ],
            [
                'Failed to convert the variable, `"qux"`, to an int',
                'qux',
                'int',
            ],
            [
                'Failed to convert the variable, `"quux"`, to a double',
                'quux',
                'double',
            ],
            [
                'Failed to convert the variable, `"corge"`, to a float',
                'corge',
                'float',
            ],
            [
                'Failed to convert the variable, [array], to a string',
                [],
                'string',
            ],
            [
                'Failed to convert the variable, `"grault"`, to an array',
                'grault',
                'array',
            ],
        ];
    }

    /**
     * @dataProvider providesExceptionMessages
     * @param mixed $value
     */
    public function testCanBeThrown(
        string $expectedExceptionMessage,
        $value,
        string $outputTypeName
    ): void {
        $this->expectException(TypeConversionFailedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        throw new TypeConversionFailedException($value, $outputTypeName);
    }
}
