<?php

namespace WebArch\BitrixIblockPropertyType;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Location\Admin\LocationHelper;
use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class LocationType
 *
 * @package WebArch\BitrixIblockPropertyType
 */
class LocationType extends IblockPropertyTypeBase
{
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
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
        ];
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public function getPropertyFieldHtml(
        array $property,
        array $value,
        array $control
    ) {
        global $APPLICATION;

        if (!Loader::includeModule('sale')) {
            return $property['VALUE'];
        }

        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'COMPONENT_TEMPLATE'     => 'search',
                'ID'                     => '',
                'CODE'                   => htmlspecialcharsbx($value['VALUE']),
                'INPUT_NAME'             => htmlspecialcharsbx($control['VALUE']),
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
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @throws LoaderException
     * @return string
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        if (!Loader::includeModule('sale')) {
            return $property['VALUE'];
        }

        return LocationHelper::getLocationStringByCode($property['VALUE']);
    }
}
