<?php

declare(strict_types=1);

namespace DanBettles\Morf;

use BadMethodCallException;
use Closure;
use ReflectionMethod;
use RuntimeException;

use function is_bool;
use function is_int;

/**
 * @phpstan-type Validator string|Closure
 */
class Validators
{
    /**
     * Integer greater than zero
     *
     * @param mixed $value
     */
    public function positiveInteger($value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * Integer greater than or equal to zero
     *
     * @param mixed $value
     */
    public function nonNegativeInteger($value): bool
    {
        return $this->positiveInteger($value) || 0 === $value;
    }

    /**
     * @phpstan-param Validator $validator
     * @param mixed $dirtyValue
     * @throws RuntimeException If the validator-closure failed to return a Boolean
     * @throws BadMethodCallException If the method cannot be invoked
     */
    public function invokeValidator($validator, $dirtyValue): bool
    {
        if ($validator instanceof Closure) {
            $returnValue = $validator($dirtyValue);

            if (!is_bool($returnValue)) {
                throw new RuntimeException('The validator-closure failed to return a Boolean');
            }

            return $returnValue;
        }

        if (__FUNCTION__ !== $validator) {
            $method = new ReflectionMethod($this, $validator);

            if ($method->isPublic()) {
                return (bool) $method->invoke($this, $dirtyValue);
            }
        }

        throw new BadMethodCallException("The method, `{$validator}`, cannot be invoked");
    }
}
