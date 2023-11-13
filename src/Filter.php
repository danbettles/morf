<?php

declare(strict_types=1);

namespace DanBettles\Morf;

use Exception;
use InvalidArgumentException;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function get_class;
use function gettype;
use function implode;
use function in_array;
use function is_array;

use const false;
use const null;
use const true;

/**
 * @phpstan-import-type Validator from Validators
 * @phpstan-type DirtyDef mixed[]
 * @phpstan-type CompleteDef array{name:string,type:string,validator?:Validator,validValues?:mixed[],default:mixed}
 * @phpstan-type Filtered array<string,mixed>
 */
class Filter
{
    /**
     * @var string[]
     */
    private const REQUIRED_DEF_KEYS = ['name'];

    /**
     * @var array<string,mixed>
     */
    private const DEFAULT_VALUES_BY_OUTPUT_TYPE = [
        TypeConverter::TYPE_BOOLEAN => false,
        TypeConverter::TYPE_INTEGER => 0,
        TypeConverter::TYPE_DOUBLE => 0.0,
        TypeConverter::TYPE_STRING => '',
        TypeConverter::TYPE_ARRAY => [],
    ];

    private TypeConverter $typeConverter;

    private Validators $validators;

    /**
     * @phpstan-var array<CompleteDef>
     */
    private array $defs;

    /**
     * @phpstan-param array<DirtyDef> $defs
     */
    public function __construct(
        TypeConverter $typeConverter,
        Validators $validators,
        array $defs
    ) {
        $this
            ->setTypeConverter($typeConverter)
            ->setValidators($validators)
            ->setDefs($defs)
        ;
    }

    /**
     * Filters the specified values
     *
     * @param array<mixed,mixed> $dirty
     * @phpstan-return Filtered
     * @throws InvalidArgumentException If a value is invalid
     */
    public function filter(array $dirty): array
    {
        $filteredValues = [];

        foreach ($this->getDefs() as $def) {
            $elementName = $def['name'];
            $defaultValue = $def['default'];

            if (!array_key_exists($elementName, $dirty)) {
                $filteredValues[$elementName] = $defaultValue;

                continue;
            }

            $typeCastDirtyValue = $this->getTypeConverter()->convert(
                $dirty[$elementName],
                $def['type']
            );

            // (Value is correct type)

            if (is_array($typeCastDirtyValue)) {
                $filteredValues[$elementName] = $typeCastDirtyValue;

                continue;
            }

            $dirtyValueIsValid = false;

            if (array_key_exists('validValues', $def)) {
                $dirtyValueIsValid = in_array($typeCastDirtyValue, $def['validValues'], true);
            } elseif (array_key_exists('validator', $def)) {
                $dirtyValueIsValid = $this->getValidators()->invokeValidator($def['validator'], $typeCastDirtyValue);
            } else {
                $dirtyValueIsValid = true;
            }

            if (!$dirtyValueIsValid) {
                throw new InvalidArgumentException("The value of `{$elementName}` is invalid");
            }

            $filteredValues[$elementName] = $typeCastDirtyValue;
        }

        return $filteredValues;
    }

    private function setTypeConverter(TypeConverter $converter): self
    {
        $this->typeConverter = $converter;

        return $this;
    }

    public function getTypeConverter(): TypeConverter
    {
        return $this->typeConverter;
    }

    private function setValidators(Validators $validators): self
    {
        $this->validators = $validators;

        return $this;
    }

    public function getValidators(): Validators
    {
        return $this->validators;
    }

    /**
     * @phpstan-param DirtyDef &$def
     * @throws InvalidArgumentException If an output-type is not supported
     */
    private function visitDefType(array &$def): void
    {
        /** @var string */
        $userTypeName = $def['type'] ?? TypeConverter::TYPE_STRING;
        $normalizedTypeName = $this->getTypeConverter()->normalizeTypeName($userTypeName);

        if (null === $normalizedTypeName) {
            throw new InvalidArgumentException("The output-type `{$userTypeName}` is not supported");
        }

        $def['type'] = $normalizedTypeName;
    }

    /**
     * @phpstan-param DirtyDef &$def
     * @throws InvalidArgumentException If the type of a default value is unacceptable
     */
    private function visitDefDefault(array &$def): void
    {
        // (The type-name is valid)
        /** @var string */
        $normalizedTypeName = $def['type'];

        if (array_key_exists('default', $def)) {
            $userDefaultValue = $def['default'];
            $defaultValueIsValid = null === $userDefaultValue || $normalizedTypeName === gettype($userDefaultValue);

            if (!$defaultValueIsValid) {
                throw new InvalidArgumentException("The type of the default value must be {$normalizedTypeName}|null");
            }
        } else {
            $def['default'] = self::DEFAULT_VALUES_BY_OUTPUT_TYPE[$normalizedTypeName];
        }
    }

    /**
     * @phpstan-param array<DirtyDef> $defs
     * @throws InvalidArgumentException If there are no defs
     * @throws InvalidArgumentException If a definition is missing one/more elements
     * @throws InvalidArgumentException If a definition is invalid
     */
    private function setDefs(array $defs): self
    {
        if (!$defs) {
            throw new InvalidArgumentException('There are no defs');
        }

        foreach ($defs as $defKey => &$def) {
            $missingKeys = array_diff(self::REQUIRED_DEF_KEYS, array_keys($def));

            if ($missingKeys) {
                $csvOfMissingKeys = implode(', ', array_map(
                    fn (string $missingKey): string => "`{$missingKey}`",
                    $missingKeys
                ));

                throw new InvalidArgumentException(
                    "Definition [{$defKey}] is missing elements: {$csvOfMissingKeys}"
                );
            }

            try {
                $this->visitDefType($def);
                $this->visitDefDefault($def);
            } catch (Exception $ex) {
                /** @phpstan-var class-string<Exception> */
                $exceptionClassName = get_class($ex);

                throw new $exceptionClassName("Definition [{$defKey}] is invalid: {$ex->getMessage()}", 0, $ex);
            }
        }

        /** @phpstan-var array<CompleteDef> $defs */

        $this->defs = $defs;

        return $this;
    }

    /**
     * @phpstan-return array<CompleteDef>
     */
    public function getDefs(): array
    {
        return $this->defs;
    }

    /**
     * @phpstan-param array<DirtyDef> $defs
     */
    public static function create(array $defs): self
    {
        return new self(
            new TypeConverter(),
            new Validators(),
            $defs
        );
    }
}
