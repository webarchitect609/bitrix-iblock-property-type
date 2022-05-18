<?php

namespace WebArch\BitrixIblockPropertyType\Exception;

use Exception;
use Throwable;

class UnsupportedControlModeException extends Exception implements BitrixIblockPropertyTypeExceptionInterface
{
    /**
     * UnsupportedControlModeException constructor.
     *
     * @param string $controlMode
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($controlMode, $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'Unsupported control mode `%s` or name-key pair could not be recognized.',
            $controlMode
        );
        parent::__construct($message, $code, $previous);
    }
}
