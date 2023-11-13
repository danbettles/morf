<?php

declare(strict_types=1);

namespace DanBettles\Morf\Tests;

use BadMethodCallException;
use Closure;
use DanBettles\Morf\Validators;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function array_merge;

use const false;
use const null;
use const true;

class ValidatorsTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $this->assertInstanceOf(Validators::class, new Validators());
    }

    /** @return array<mixed[]> */
    public function providesPositiveIntegers(): array
    {
        return [
            [true, 21],

            [false, -1],  // No, because negative
            [false, 0.0],  // Not an integer
            [false, 21.0],  // ditto
            [false, '0'],  // Not an integer
            [false, '21'],  // ditto
            [false, '0.0'],  // Definitely not
            [false, '21.0'],  // ditto
        ];
    }

    /**
     * @dataProvider providesPositiveIntegers
     * @param mixed $value
     */
    public function testPositiveintegerReturnsTrueIfTheValueIsAPositiveInteger(
        bool $expected,
        $value
    ): void {
        $this->assertSame(
            $expected,
            (new Validators())->positiveInteger($value)
        );
    }

    /** @return array<mixed[]> */
    public function providesNonNegativeIntegers(): array
    {
        return array_merge($this->providesPositiveIntegers(), [
            [true, 0],
        ]);
    }

    /**
     * @dataProvider providesNonNegativeIntegers
     * @param mixed $value
     */
    public function testNonnegativeintegerReturnsTrueIfTheValueIsANonNegativeNumber(
        bool $expected,
        $value
    ): void {
        $this->assertSame(
            $expected,
            (new Validators())->nonNegativeInteger($value)
        );
    }

    public function testInvokevalidatorInvokesTheMethodWithTheSpecifiedName(): void
    {
        $validatorsMock = $this
            ->getMockBuilder(Validators::class)
            ->onlyMethods(['positiveInteger'])
            ->getMock()
        ;

        $validatorsMock
            ->expects($this->once())
            ->method('positiveInteger')
            ->with(123)
            ->willReturn(true)
        ;

        /** @var Validators $validatorsMock */

        $this->assertTrue($validatorsMock->invokeValidator('positiveInteger', 123));
    }

    public function testInvokevalidatorInvokesOnlyPublicMethods(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The method, `uninvokeableMethod`, cannot be invoked');

        $validators = new class extends Validators {
            /** @phpstan-ignore-next-line */
            private function uninvokeableMethod(): bool
            {
                return false;
            }
        };

        $validators->invokeValidator('uninvokeableMethod', 'anything');
    }

    public function testInvokevalidatorCannotInvokeItself(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The method, `invokeValidator`, cannot be invoked');

        (new Validators())->invokeValidator('invokeValidator', 'anything');
    }

    /** @return array<mixed[]> */
    public function providesValidatorClosures(): array
    {
        return [
            [
                true,
                fn ($value) => 3 === $value,
                3,
            ],
            [
                false,
                fn ($value) => 3 === $value,
                0,
            ],
        ];
    }

    /**
     * @dataProvider providesValidatorClosures
     * @param mixed $dirtyValue
     */
    public function testInvokevalidatorWillInvokeAClosure(
        bool $expected,
        Closure $validator,
        $dirtyValue
    ): void {
        $valueIsValid = (new Validators())->invokeValidator($validator, $dirtyValue);

        $this->assertSame($expected, $valueIsValid);
    }

    public function testInvokevalidatorThrowsAnExceptionIfAClosureFailsToReturnABoolean(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The validator-closure failed to return a Boolean');

        (new Validators())->invokeValidator(fn () => null, 'anything');
    }
}
