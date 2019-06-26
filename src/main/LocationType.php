<?php

namespace WebArch\BitrixIblockPropertyType;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\Admin\LocationHelper;
use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class Location
 * @package Adv\AdvApplication\IblockProperty
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
            'GetAdminListViewHTML' => [
                $this,
                'getAdminListViewHTML',
            ],
            'GetPropertyFieldHtml' => [
                $this,
                'getPropertyFieldHtml',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return 'sale_location';
    }

    /**
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return bool|string
     * @throws ArgumentTypeException
     * @throws LoaderException
     * @throws SystemException
     */
    public function getPropertyFieldHtml(
        /** @noinspection PhpUnusedParameterInspection */
        $property,
        $value,
        $control
    )
    {
        $result = false;
        if (Loader::includeModule('sale')) {
            global $APPLICATION;
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
            $result = ob_get_clean();
        }

        return $result;
    }

    /**
     * @param array $property
     * @param array $value
     * @param array $control
     *
     * @return bool|string
     * @throws LoaderException
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        $result = false;
        if (Loader::includeModule('sale')) {
            $result = LocationHelper::getLocationStringByCode($property['VALUE']);
        }

        return $result;
    }
}

