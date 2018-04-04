<?php

namespace WebArch\BitrixIblockPropertyType;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;

/**
 * Class YesNoType
 *
 * Тип свойства "Да/Нет", который также выводит состояние "не задано", когда в базе данных для элемента инфоблока
 * значение свойства ещё не определено. Это должно помочь избежать ситуации, когда в админке отображалось бы "Нет", а
 * при фильтрации по значению 0 требуемые элементы не попадают в выборку.
 *
 * @package WebArch\BitrixIblockPropertyType
 */
class YesNoType extends IblockPropertyTypeBase
{
    const CHECKED_ATTR = ' checked="checked" ';

    const SELECTED_ATTR = ' selected="selected" ';

    /**
     * @inheritdoc
     */
    public function getPropertyType()
    {
        return 'N';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Признак "Да/Нет"';
    }

    /**
     * @inheritdoc
     */
    public function getCallbacksMapping()
    {
        return [
            'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
            'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
            'ConvertToDB'          => [$this, 'convertToDB'],
            'ConvertFromDB'        => [$this, 'convertFromDB'],
            'GetAdminFilterHTML'   => [$this, 'getAdminFilterHTML'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAdminListViewHTML(array $property, array $value, array $controlName)
    {
        $isYes = self::isYes($value);
        if (true === $isYes) {
            return 'Да';
        } elseif (false === $isYes) {
            return 'Нет';
        }

        return 'не задано';
    }

    /**
     * @inheritdoc
     */
    public function getPropertyFieldHtml(array $property, array $value, array $controlName)
    {
        $isYes = self::isYes($value);
        $checked = $isYes ? self::CHECKED_ATTR : '';

        $return = '<input type="hidden" name="' . $controlName['VALUE'] . '" value="0" />';
        $return .= '<input'
            . $checked
            . ' type="checkbox" name="'
            . $controlName['VALUE']
            . '" id="'
            . $controlName['VALUE']
            . '" value="1" />';

        if (is_null($isYes)) {
            $return .= '<br>сейчас: не задано';
        }

        if ($property['WITH_DESCRIPTION'] == 'Y') {
            $return .= '<div><input type="text" size="'
                . $property['COL_COUNT']
                . '" name="'
                . $controlName['DESCRIPTION']
                . '" value="'
                . htmlspecialchars($value['DESCRIPTION'])
                . '" /></div>';
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getAdminFilterHTML(array $property, array $controlName)
    {
        $curValue = null;
        if (isset($GLOBALS[$controlName['VALUE']])) {
            $curValue = (int)$GLOBALS[$controlName['VALUE']];
        }

        /** @noinspection HtmlUnknownAttribute */
        $html =
            '<select name="%s">'
            . '<option value="" >(любой)</option>'
            . '<option value="1" %s >Да</option>'
            . '<option value="0" %s >Нет</option>'
            . '</select>';

        return sprintf(
            $html,
            $controlName['VALUE'],
            1 === $curValue ? self::SELECTED_ATTR : '',
            0 === $curValue ? self::SELECTED_ATTR : ''
        );
    }

    /**
     * @inheritdoc
     */
    public function convertToDB(array $property, array $value)
    {
        $value['VALUE'] = self::normalize($value);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function convertFromDB(array $property, array $value)
    {
        $value['VALUE'] = self::isYes($value);

        return $value;
    }

    /**
     * @param array $value
     *
     * @return int
     */
    public static function normalize(array $value)
    {
        return (int)self::isYes($value);
    }

    /**
     * @param array $value
     *
     * @return bool|null
     */
    public static function isYes(array $value)
    {
        if ((int)$value['VALUE'] == 1) {
            return true;
        } elseif ((int)$value['VALUE'] == 0) {
            return false;
        }

        return null;
    }

}
