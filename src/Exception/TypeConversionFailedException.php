<?php

declare(strict_types=1);

namespace DanBettles\Morf\Exception;

use RuntimeException;
use Throwable;

use function gettype;
use function in_array;
use function sprintf;

use const null;

class TypeConversionFailedException extends RuntimeException
{
    /**
     * @param mixed $value
     * @param int $code
     */
    public function __construct(
        $value,
        string $outputTypeName,
        $code = 0,
        Throwable $previous = null
    ) {
        $serializedValue = null;

        switch (gettype($value)) {
            case 'array':
                $serializedValue = '[array]';
                break;
            case 'string':
                $serializedValue = "`\"{$value}\"`";
                break;
            default:
                $serializedValue = '[unsupported]';
        }

        $indefiniteArticle = in_array($outputTypeName[0], ['a', 'e', 'i', 'o', 'u', 'h'])
            ? 'an'
            : 'a'
        ;

        $message = sprintf(
            'Failed to convert the variable, %s, to %s %s',
            $serializedValue,
            $indefiniteArticle,
            $outputTypeName
        );

        parent::__construct($message, $code, $previous);
    }
}
