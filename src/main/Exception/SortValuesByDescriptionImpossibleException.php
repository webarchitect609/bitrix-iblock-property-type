<?php

namespace WebArch\BitrixIblockPropertyType\Exception;

use RuntimeException;

/**
 * Когда сортировка значений множественного свойства на основании значений в DESCRIPTION невозможна.
 */
class SortValuesByDescriptionImpossibleException extends RuntimeException implements BitrixIblockPropertyTypeExceptionInterface
{
}
