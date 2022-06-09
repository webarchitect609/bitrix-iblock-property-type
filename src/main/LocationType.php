<?php

/** @noinspection PhpMissingReturnTypeInspection */

namespace WebArch\BitrixIblockPropertyType;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Location\Admin\LocationHelper;
use HtmlObject\Element;
use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use WebArch\BitrixIblockPropertyType\Exception\ModuleNotFoundException;

/**
 * Class LocationType
 *
 * @package WebArch\BitrixIblockPropertyType
 */
class LocationType extends IblockPropertyTypeBase
{
    private const MODULE_SALE = 'sale';

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return self::PROPERTY_TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Привязка к местоположению';
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML'      => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml'      => [$this, 'getPropertyFieldHtml'],
            'GetPropertyFieldHtmlMulty' => [$this, 'getPropertyFieldHtmlMulty'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control) {
        try {
            $this->includeIblockElementAdminLangFile();
            $descInput = '';
            if (key_exists('WITH_DESCRIPTION', $property) && 'Y' === $property['WITH_DESCRIPTION']) {
                $descInput = $this->getDescriptionInput(
                    $control['DESCRIPTION'],
                    $value['DESCRIPTION']
                );
            }

            return $this->getValueInputWithAutoComplete($value['VALUE'], $control['VALUE']) . $descInput;
        } catch (LoaderException|ModuleNotFoundException $exception) {
            return $this->getErrorSpan($exception->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getPropertyFieldHtmlMulty(array $property, array $valueList, array $control)
    {
        try {
            $this->includeIblockElementAdminLangFile();
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
                    ['VALUE' => '', 'DESCRIPTION' => ''],
                    $control
                );
            }

            /**
             * К этой таблице НЕЛЬЗЯ добавлять кнопку "Добавить"/"Ещё", т.к. крайне сложно
             * скопировать поле поиска местоположения, не сломав его.
             */
            return Element::table(
                implode('', $rowList),
                ['width' => '100%']
            );
        } catch (LoaderException|ModuleNotFoundException $exception) {
            return $this->getErrorSpan($exception->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        try {
            $this->assertSaleModuleIncluded();

            return LocationHelper::getLocationStringByCode($property['VALUE']);
        } catch (LoaderException|ModuleNotFoundException $exception) {
            return $this->getErrorSpan($exception->getMessage());
        }
    }

    /**
     * @param string $valueId
     * @param array $property
     * @param array $singleValue
     * @param array $control
     *
     * @throws LoaderException
     * @throws ModuleNotFoundException
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getMultiRow(string $valueId, array $property, array $singleValue, array $control): Element
    {
        $descriptionCell = '';
        if (key_exists('WITH_DESCRIPTION', $property) && 'Y' === $property['WITH_DESCRIPTION']) {
            $descriptionCell = $this->getDescriptionInput(
                $control['VALUE'] . '[' . $valueId . '][DESCRIPTION]',
                $singleValue['DESCRIPTION']
            );
        }

        return Element::tr(
            Element::td(
                $this->getValueInputWithAutoComplete(
                    $singleValue['VALUE'],
                    $control['VALUE'] . '[' . $valueId . '][VALUE]'
                )
                . $descriptionCell
            )
        );
    }

    /**
     * @param null|string $valueValue
     * @param string $inputName
     *
     * @throws LoaderException
     * @throws ModuleNotFoundException
     * @return false|string
     */
    private function getValueInputWithAutoComplete(?string $valueValue, string $inputName): string
    {
        global $APPLICATION;

        $this->assertSaleModuleIncluded();

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'COMPONENT_TEMPLATE'     => 'search',
                'ID'                     => '',
                'CODE'                   => htmlspecialcharsbx($valueValue),
                'INPUT_NAME'             => htmlspecialcharsbx($inputName),
                'PROVIDE_LINK_BY'        => 'code',
                'JSCONTROL_GLOBAL_ID'    => '',
                'JS_CALLBACK'            => '',
                'SEARCH_BY_PRIMARY'      => 'Y',
                'EXCLUDE_SUBTREE'        => '',
                'FILTER_BY_SITE'         => 'Y',
                'SHOW_DEFAULT_LOCATIONS' => 'Y',
                'CACHE_TYPE'             => 'A',
                'CACHE_TIME'             => '36000000',
            ],
            false
        );

        return ob_get_clean();
    }

    /**
     * @param string $name
     * @param null|string $value
     *
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getDescriptionInput(string $name, ?string $value): Element
    {
        return Element::label(
            GetMessage('IBLOCK_ELEMENT_EDIT_PROP_DESC_1')
            . Element::input(
                null,
                [
                    'name'  => $name,
                    'value' => (string)$value,
                ]
            )
        );
    }

    /**
     * @throws LoaderException
     * @throws ModuleNotFoundException
     * @return void
     */
    private function assertSaleModuleIncluded(): void
    {
        if (!Loader::includeModule(self::MODULE_SALE)) {
            throw new ModuleNotFoundException(
                sprintf(
                    'Модуль %s не установлен.',
                    self::MODULE_SALE
                )
            );
        }
    }

    /**
     * @param string $message
     *
     * @return Element
     * @noinspection PhpUndefinedMethodInspection
     */
    private function getErrorSpan(string $message): Element
    {
        return Element::span(
            $message,
            ['style' => 'color: red; font-weight: bold;']
        );
    }
}
