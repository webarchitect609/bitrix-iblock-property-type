<?php

/** @noinspection PhpMissingReturnTypeInspection */

namespace WebArch\BitrixIblockPropertyType;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EventManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CIBlockElement;
use CIBlockProperty;
use HtmlObject\Element;
use Psr\Log\LoggerAwareTrait;
use ReflectionException;
use UnexpectedValueException;
use WebArch\BitrixCache\Cache;
use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use WebArch\BitrixIblockPropertyType\Exception\NoTableNameException;
use WebArch\BitrixIblockPropertyType\Exception\SortValuesByDescriptionImpossibleException;

class DirectorySortableType extends IblockPropertyTypeBase
{
    use LoggerAwareTrait;

    const SETTING_TABLE_NAME = 'TABLE_NAME';

    const USER_TYPE_SETTINGS = 'USER_TYPE_SETTINGS';

    /**
     * @var array<int, bool> [ 123 => true]
     */
    private $byIdIndex = [];

    /**
     * @var array<int, array<string, int>> [ (int)$iblockId => [ (string)$propCode => (int)$propId ] ]
     */
    private $propIdByIblockIdAndCodeIndex = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        EventManager::getInstance()
                    ->addEventHandler(
                        'iblock',
                        'OnAfterIBlockElementSetPropertyValues',
                        [$this, 'sortValuesAfterSetPropertyValues']
                    );
        EventManager::getInstance()
                    ->addEventHandler(
                        'iblock',
                        'OnAfterIBlockElementSetPropertyValuesEx',
                        [$this, 'sortValuesAfterSetPropertyValuesEx']
                    );
    }

    /**
     * @inheritDoc
     */
    public function getPropertyType(): string
    {
        return self::PROPERTY_TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Справочник(с сортировкой множества)';
    }

    /**
     * @inheritDoc
     */
    public function getCallbacksMapping(): array
    {
        return [
            'PrepareSettings'           => [$this, 'prepareSettings'],
            'GetSettingsHTML'           => [$this, 'getSettingsHTML'],
            'GetPropertyFieldHtml'      => [$this, 'getPropertyFieldHtml'],
            'GetPropertyFieldHtmlMulty' => [$this, 'getPropertyFieldHtmlMulty'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function prepareSettings(array $property)
    {
        if (
            !key_exists(self::SETTING_TABLE_NAME, $property[self::USER_TYPE_SETTINGS])
            || !is_string($property[self::USER_TYPE_SETTINGS][self::SETTING_TABLE_NAME])
        ) {
            $property[self::USER_TYPE_SETTINGS][self::SETTING_TABLE_NAME] = '';
        }

        return $property;
    }

    /**
     * @inheritDoc
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getSettingsHTML(array $property, array $control, array $propertyFields)
    {
        $options = [Element::option('(не установлено)', ['value' => ''])];
        $result = HighloadBlockTable::query()
                                    ->setSelect(['*', 'NAME_LANG' => 'LANG.NAME'])
                                    ->setOrder(['NAME_LANG' => 'ASC', 'NAME' => 'ASC'])
                                    ->exec();
        while ($row = $result->fetch()) {
            $attr = [
                'value' => htmlspecialcharsbx($row["TABLE_NAME"]),
            ];
            if ($property[self::USER_TYPE_SETTINGS][self::SETTING_TABLE_NAME] === $attr['value']) {
                $attr['selected'] = 'selected';
            }
            $options[] = Element::option(
                sprintf(
                    '%s (%s)',
                    $row['NAME_LANG'] != '' ? $row['NAME_LANG'] : $row['NAME'],
                    $row['TABLE_NAME']
                ),
                $attr
            );
        }

        return Element::tr(
            Element::td('Выберите справочник:')
            . Element::td(
                Element::select(
                    implode('', $options),
                    [
                        'name'     => sprintf(
                            '%s[%s]',
                            $control['NAME'],
                            self::SETTING_TABLE_NAME
                        ),
                        'size'     => 1,
                        'required' => 'required',
                    ]
                )
            )
        );
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        try {
            return $this->getSelect(
                $property[self::USER_TYPE_SETTINGS][self::SETTING_TABLE_NAME],
                $value['VALUE'],
                $control['VALUE']
            );
        } catch (NoTableNameException $exception) {
            return $this->getNoTableNameErrorHtml($exception);
        }
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getPropertyFieldHtmlMulty(array $property, array $valueList, array $control)
    {
        try {
            $rowList = [];
            // Существующие значения
            foreach ($valueList as $valueId => $singleValue) {
                $rowList[] = $this->getMultiRow(
                    $valueId,
                    $property,
                    $singleValue,
                    $control
                );
            }
            // Новые значения
            for ($n = 0; $n < ($property['MULTIPLE_CNT'] ?? 1); $n++) {
                $rowList[] = $this->getMultiRow(
                    'n' . $n,
                    $property,
                    ['VALUE' => '', 'DESCRIPTION' => 50],
                    $control
                );
            }
            $tableId = 'tb' . md5($control['VALUE'] . 'VALUE');

            return Element::table(
                    Element::th('Значение')
                    . Element::th('Сортировка')
                    . implode('', $rowList),
                    ['id' => $tableId]
                )
                . Element::input(
                    null,
                    [
                        'type'    => 'button',
                        'value'   => 'Ещё',
                        'onclick' => sprintf(
                            'if(typeof window.addNewRow === \'function\') {addNewRow(\'%s\', -1)}'
                            . 'else if(typeof window.addNewTableRow === \'function\'){addNewTableRow(\'%s\', 1, /\[(n)([0-9]*)\]/g, 2)}'
                            . 'else if(typeof BX.IBlock.Tools.addNewRow === \'function\'){BX.IBlock.Tools.addNewRow(\'%s\', -1)}'
                            . 'else { window.alert(\'Unable to find «addNewRow» JavaScript function.\') }',
                            $tableId,
                            $tableId,
                            $tableId
                        ),
                    ]
                );
        } catch (NoTableNameException $exception) {
            return $this->getNoTableNameErrorHtml($exception);
        }
    }

    /**
     * @param int $elementId
     * @param int $iblockId
     * @param array $propertyValues
     * @param false|string $propertyCode
     *
     * @throws ReflectionException
     * @return void
     */
    public function sortValuesAfterSetPropertyValues(
        int   $elementId,
        int   $iblockId,
        array $propertyValues,
              $propertyCode
    ) {
        // Множество свойств
        if (false === $propertyCode) {
            foreach ($this->filterSupportedPropsById($propertyValues, $iblockId) as $propIdOrCode => $values) {
                $this->doSortValuesAfterAnySetPropValues($elementId, $iblockId, $propIdOrCode, $values);
            }
        }
        // Одно свойство по коду
        if (is_string($propertyCode) && $this->isSupportedIblockAndCode($iblockId, $propertyCode)) {
            $this->doSortValuesAfterAnySetPropValues(
                $elementId,
                $iblockId,
                $propertyCode,
                $propertyValues
            );
        }
    }

    /**
     * @param int $elementId
     * @param int $iblockId
     * @param array $propertyValues
     *
     * @throws ReflectionException
     * @return void
     */
    public function sortValuesAfterSetPropertyValuesEx(int $elementId, int $iblockId, array $propertyValues): void
    {
        foreach ($this->filterSupportedPropsById($propertyValues, $iblockId) as $propIdOrCode => $values) {
            $this->doSortValuesAfterAnySetPropValues($elementId, $iblockId, $propIdOrCode, $values);
        }
    }

    /**
     * @param int $elementId
     * @param int $iblockId
     * @param int|string $propIdOrCode
     * @param false|array $values
     *
     * @return void
     */
    private function doSortValuesAfterAnySetPropValues(int $elementId, int $iblockId, $propIdOrCode, $values): void
    {
        if (!is_array($values)) {
            return;
        }
        /*
         * Изменение порядка следования невозможно без удаления прежних значений.
         * Поэтому от id значений нужно избавиться сразу.
         */
        $nonEmptyNakedValues = $this->clearValueIds($this->filterNonEmptyValues($values));
        $sortedValues = $this->sortValuesUsingDescriptionAsSort($nonEmptyNakedValues);
        // Защита от зацикливания: если нет создания новых значений и в базе та же самая сортировка.
        if ($nonEmptyNakedValues === $sortedValues) {
            return;
        }
        // Предварительное принудительное удаление, иначе они не перестроятся.
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [$propIdOrCode => false]);
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [$propIdOrCode => $sortedValues]);
    }

    /**
     * @param string $hlTableName
     * @param int $limit
     *
     * @throws NoTableNameException
     * @throws ReflectionException
     * @return array<string, string>
     */
    private function getReferenceItemList(string $hlTableName, int $limit = 1000): array
    {
        if (trim($hlTableName) == '') {
            throw new NoTableNameException(
                'Не выбран справочник в настройках свойства.'
            );
        }

        return Cache::create()
                    ->setPathByClass(static::class)
                    ->setKey(
                        sprintf(
                            '%s_%s_%d',
                            __FUNCTION__,
                            $hlTableName,
                            $limit
                        )
                    )
                    ->setTTL(120)
                    ->callback(
                        static function () use ($hlTableName, $limit) {
                            $hlBlockFields = HighloadBlockTable::query()
                                                               ->setSelect(['TABLE_NAME', 'NAME', 'ID'])
                                                               ->setFilter(['=TABLE_NAME' => $hlTableName])
                                                               ->exec()
                                                               ->fetch();
                            $dataManager = HighloadBlockTable::compileEntity($hlBlockFields)
                                                             ->getDataClass();
                            $result = $dataManager::query()
                                                  ->setSelect(['*'])
                                                  ->setOrder(['UF_NAME' => 'ASC'])
                                                  ->setLimit($limit)
                                                  ->exec();
                            $list = [];
                            while ($row = $result->fetch()) {
                                if (key_exists('UF_XML_ID', $row)) {
                                    $id = trim($row['UF_XML_ID']);
                                } elseif (key_exists('ID', $row)) {
                                    $id = trim($row['ID']);
                                } else {
                                    continue;
                                }
                                if (key_exists('UF_NAME', $row)) {
                                    $name = sprintf(
                                        '%s [%s]',
                                        $row['UF_NAME'],
                                        $id
                                    );
                                } else {
                                    $name = $id;
                                }
                                $list[$id] = $name;
                            }

                            return $list;
                        }
                    );
    }

    /**
     * @param string $hlTableName
     * @param string $valueValue
     * @param string $selectName
     *
     * @throws NoTableNameException
     * @throws ReflectionException
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getSelect(string $hlTableName, string $valueValue, string $selectName): Element
    {
        return Element::select(
            implode(
                '',
                $this->getOptionList($hlTableName, $valueValue)
            ),
            ['name' => $selectName]
        );
    }

    /**
     * @param string $hlTableName
     * @param string $valueValue
     *
     * @throws NoTableNameException
     * @throws ReflectionException
     * @return Element[]
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getOptionList(string $hlTableName, string $valueValue): array
    {
        $list = [Element::option('(не установлено)', ['value' => ''])];
        foreach ($this->getReferenceItemList($hlTableName) as $id => $name) {
            $attributes = ['value' => $id];
            if ($id == $valueValue) {
                $attributes['selected'] = 'selected';
            }
            $list[] = Element::option($name, $attributes);
        }

        return $list;
    }

    /**
     * @param string $valueId
     * @param array $property
     * @param array $singleValue
     * @param array $control
     *
     * @throws NoTableNameException
     * @throws ReflectionException
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getMultiRow(string $valueId, array $property, array $singleValue, array $control): Element
    {
        return Element::tr(
            Element::td(
                Element::table(
                    Element::tr(
                        Element::td(
                            $this->getSelect(
                                $property[self::USER_TYPE_SETTINGS][self::SETTING_TABLE_NAME],
                                $singleValue['VALUE'],
                                $control['VALUE'] . '[' . $valueId . '][VALUE]'
                            )
                        )
                        .
                        Element::td(
                            $this->getInput(
                                (int)$singleValue['DESCRIPTION'],
                                $control['VALUE'] . '[' . $valueId . '][DESCRIPTION]'
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @param int $sortValue
     * @param string $inputName
     *
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getInput(int $sortValue, string $inputName): Element
    {
        return Element::input(
            null,
            [
                'name'  => $inputName,
                'value' => $sortValue,
            ]
        );
    }

    /**
     * @param $exception
     *
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getNoTableNameErrorHtml($exception): Element
    {
        return Element::p(
            $exception->getMessage(),
            ['style' => 'color: red; font-weight: bold;']
        );
    }

    /**
     * Сортирует значения, используя описание значения как сортировку.
     *
     * @param array<int, array<string, string>> $valueList [
     * (int)$valueId1 => [
     *   'VALUE'       => (string)$value1,
     *   'DESCRIPTION' => (int)$sort1
     *   ],
     * ...
     * ]
     *
     * @return array<int, array<string, string>>
     */
    private function sortValuesUsingDescriptionAsSort(array $valueList): array
    {
        $keepKeys = array_keys($valueList);
        $justSorted = $valueList;
        try {
            usort(
                $justSorted,
                static function ($a, $b) {
                    if (
                        is_array($a)
                        && key_exists('DESCRIPTION', $a)
                        && is_array($b)
                        && key_exists('DESCRIPTION', $b)
                    ) {
                        return (int)$a['DESCRIPTION'] <=> (int)$b['DESCRIPTION'];
                    }
                    /*
                     * Опасно возвращать 0, т.к. очерёдность элементов с одинаковой сортировкой в PHP < 8.0
                     * не определена, и может потенциально возникнуть бесконечная рекурсия из-за перестроения.
                     * Лучше отменить сортировку.
                     */
                    throw new SortValuesByDescriptionImpossibleException();
                }
            );
        } catch (SortValuesByDescriptionImpossibleException $exception) {
            // Сортировка невозможна: сохранить прежний порядок.
            $justSorted = $valueList;
        }

        $sortedValueList = [];
        foreach ($justSorted as $value) {
            $sortedValueList[array_shift($keepKeys)] = $value;
        }

        return $sortedValueList;
    }

    /**
     * Возвращает индексы, по которым можно определять по ID свойства или по ID инфоблока и коду свойства его
     * принадлежность к данному типу.
     *
     * @throws ReflectionException
     * @return array<int, array> [ (array)$byId, (array)$byIblockIdAndCode ]
     */
    private function getPropIndices(): array
    {
        return Cache::create()
                    ->setPathByClass(static::class)
                    ->setKey(__FUNCTION__)
                    ->callback(
                        function () {
                            $result = CIBlockProperty::GetList(
                                [],
                                [
                                    'USER_TYPE' => $this->getUserType(),
                                    'MULTIPLE'  => 'Y',
                                ]
                            );
                            $byId = [];
                            $byIblockIdAndCode = [];
                            while ($row = $result->Fetch()) {
                                $byId[(int)$row['ID']] = true;
                                if (trim($row['CODE']) != '') {
                                    $byIblockIdAndCode[(int)$row['IBLOCK_ID']][trim($row['CODE'])] = (int)$row['ID'];
                                }
                            }

                            return [$byId, $byIblockIdAndCode];
                        }
                    );
    }

    /**
     * @throws ReflectionException
     * @return void
     */
    private function refreshPropIndices(): void
    {
        [$this->byIdIndex, $this->propIdByIblockIdAndCodeIndex] = $this->getPropIndices();
    }

    /**
     * @param int $propId
     *
     * @throws ReflectionException
     * @return bool
     */
    private function isSupportedPropId(int $propId): bool
    {
        $this->refreshPropIndices();

        return key_exists($propId, $this->byIdIndex);
    }

    /**
     * @param int $iblockId
     * @param string $propCode
     *
     * @throws ReflectionException
     * @return bool
     */
    private function isSupportedIblockAndCode(int $iblockId, string $propCode): bool
    {
        $this->refreshPropIndices();

        return key_exists($iblockId, $this->propIdByIblockIdAndCodeIndex)
            && is_array($this->propIdByIblockIdAndCodeIndex[$iblockId])
            && trim($propCode) != ''
            && key_exists($propCode, $this->propIdByIblockIdAndCodeIndex[$iblockId]);
    }

    /**
     * @param array<int|string, array> $propertyValues
     * @param int $iblockId
     *
     * @throws ReflectionException
     * @return array
     */
    private function filterSupportedPropsById(array $propertyValues, int $iblockId): array
    {
        return array_filter(
            $propertyValues,
            function ($propIdOrCode) use ($iblockId) {
                if (is_int($propIdOrCode)) {
                    return $this->isSupportedPropId($propIdOrCode);
                }
                if (is_string($propIdOrCode)) {
                    return $this->isSupportedIblockAndCode($iblockId, $propIdOrCode);
                }
                throw new UnexpectedValueException(
                    sprintf(
                        'Expected key of property values to be int or string, but got %s `%s`',
                        gettype($propIdOrCode),
                        $propIdOrCode
                    )
                );
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Удаляет пустые значения свойства, которые не могут быть сохранены в базе данных.
     *
     * @param array $propertyValues
     *
     * @return array
     */
    private function filterNonEmptyValues(array $propertyValues): array
    {
        return array_filter(
            $propertyValues,
            static function ($value) {
                // "Распаковка" значения из массива, если требуется.
                if (is_array($value) && key_exists('VALUE', $value)) {
                    $realValue = $value['VALUE'];
                } else {
                    $realValue = $value;
                }

                return $realValue !== false && trim($realValue) !== '';
            }
        );
    }

    /**
     * Стирает ID значений свойств.
     *
     * @param array $propertyValues
     *
     * @return array
     */
    private function clearValueIds(array $propertyValues): array
    {
        $n = 0;
        $clear = [];
        foreach ($propertyValues as $value) {
            $clear['n' . $n++] = $value;
        }

        return $clear;
    }
}
