<?php

declare(strict_types=1);

namespace DanBettles\Morf;

use DanBettles\Morf\Exception\TypeConversionFailedException;
use InvalidArgumentException;

use function array_key_exists;
use function filter_var;
use function in_array;
use function is_array;
use function is_string;

use const FILTER_DEFAULT;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use const null;
use const true;

class TypeConverter
{
    /** @var string */
    public const TYPE_BOOLEAN = 'boolean';
    /** @var string */
    public const TYPE_INTEGER = 'integer';
    /** @var string */
    public const TYPE_DOUBLE = 'double';
    /** @var string */
    public const TYPE_STRING = 'string';
    /** @var string */
    public const TYPE_ARRAY = 'array';

    /**
     * @var array<string,string>
     */
    private const TYPE_ALIASES = [
        'bool' => self::TYPE_BOOLEAN,
        'int' => self::TYPE_INTEGER,
        'float' => self::TYPE_DOUBLE,
    ];

    /**
     * Only the types we could possibly create from values received in the request
     *
     * @var string[]
     */
    private const GETTYPE_TYPES = [
        self::TYPE_BOOLEAN,
        self::TYPE_INTEGER,
        self::TYPE_DOUBLE,
        self::TYPE_STRING,
        self::TYPE_ARRAY,
    ];

    /**
     * @var array<string,int>
     */
    private const TYPE_FILTERS = [
        self::TYPE_BOOLEAN => FILTER_VALIDATE_BOOLEAN,
        self::TYPE_INTEGER => FILTER_VALIDATE_INT,
        self::TYPE_DOUBLE => FILTER_VALIDATE_FLOAT,
        self::TYPE_STRING => FILTER_DEFAULT,
    ];

    /**
     * Normalizes the type-name to one of the names listed in `GETTYPE_TYPES`, or returns `null` if the type isn't supported
     */
    public function normalizeTypeName(string $typeName): ?string
    {
        if (in_array($typeName, self::GETTYPE_TYPES, true)) {
            return $typeName;
        }

        if (array_key_exists($typeName, self::TYPE_ALIASES)) {
            return self::TYPE_ALIASES[$typeName];
        }

        return null;
    }

    /**
     * Wrapper for `filter_var()`
     *
     * @return mixed `null` on failure
     */
    private function filterVar(string $var, int $filter)
    {
        if (FILTER_VALIDATE_BOOLEAN === $filter) {
            return in_array($var, ['1', '0'], true)
                ? (bool) $var
                : null
            ;
        }

        return filter_var($var, $filter, FILTER_NULL_ON_FAILURE);
    }

    /**
     * Converts the variable to the type with the specified name
     *
     * @param string|mixed[] $var
     * @return mixed
     * @throws TypeConversionFailedException If it can't, or failed to, convert the variable to the specified type
     * @todo Support names like `"string[]"`, not simply `"array"`; validate each element
     */
    private function convertType(
        $var,
        string $normalizedTypeName
    ) {
        if (self::TYPE_ARRAY === $normalizedTypeName) {
            // (Only an array can be 'converted' to an array)
            if (is_array($var)) {
                return $var;
            }
        } else {
            // (We can convert only string-type variables at this point)
            if (is_string($var)) {
                $phpFilterId = self::TYPE_FILTERS[$normalizedTypeName];
                $filteredVar = $this->filterVar($var, $phpFilterId);

                if (null !== $filteredVar) {
                    return $filteredVar;
                }
            }
        }

        throw new TypeConversionFailedException($var, $normalizedTypeName);
    }

    /**
     * @param mixed $incomingValue
     * @return mixed
     * @throws InvalidArgumentException If the incoming value is neither a string nor an array
     * @throws InvalidArgumentException If the type-name is invalid
     */
    public function convert(
        $incomingValue,
        string $typeName
    ) {
        if (!is_string($incomingValue) && !is_array($incomingValue)) {
            throw new InvalidArgumentException('The incoming value is neither a string nor an array');
        }

        $normalizedTypeName = $this->normalizeTypeName($typeName);

        if (null === $normalizedTypeName) {
            throw new InvalidArgumentException('The type-name is invalid');
        }

        return $this->convertType($incomingValue, $normalizedTypeName);
    }
}
