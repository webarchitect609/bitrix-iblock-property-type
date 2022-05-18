<?php

namespace WebArch\BitrixIblockPropertyType\Exception;

use Exception;

/**
 * Если не задана таблица для связки с HL-блоком в качестве справочника.
 */
class NoTableNameException extends Exception implements BitrixIblockPropertyTypeExceptionInterface
{
}
