<?php

namespace WebArch\BitrixIblockPropertyType\Abstraction;

/**
 * Interface IblockPropertyTypeInterface
 *
 * Интерфейс типа свойства, который помогает объявить требуемые методы, а благодаря продуманным php-doc блокам не
 * лазить по документации Битрикса и даже быть в курсе недокументированных особенностей последнего.
 *
 * Также интерфейс помогает понять и следовать подходу с отказом от статических методов в пользу классического ООП и
 * полиморфизма.
 *
 * @package WebArch\BitrixIblockPropertyType\Abstraction
 */
interface IblockPropertyTypeInterface
{
    /**
     * Инициализирует тип свойства, добавляя вызов getUserTypeDescription() при событии
     * iblock::OnIBlockPropertyBuildList
     *
     * @return void
     */
    public function init();

    /**
     * Возвращает массив с описанием типа свойства и связки с реализацией своеобразного "интерфейса" из абстрактных
     * операций, которые использует Битрикс.
     *
     * Например,
     *
     * [
     *    'PROPERTY_TYPE'        => 'N',
     *    'USER_TYPE'            => 'YesNoPropertyType',
     *    'DESCRIPTION'          => 'Признак "Да/Нет"',
     *    'GetAdminListViewHTML' => [$this, 'getAdminListViewHTML'],
     *    'GetPropertyFieldHtml' => [$this, 'getPropertyFieldHtml'],
     *    'ConvertToDB'          => [$this, 'convertToDB'],
     *    'ConvertFromDB'        => [$this, 'convertFromDB'],
     *    'GetAdminFilterHTML'   => [$this, 'getAdminFilterHTML'],
     * ]
     *
     *
     * TODO Протестировать утверждение "У свойств, созданных клиентом, обязан быть статическим при использовании php7"
     * ( https://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/GetUserTypeDescription.php )
     *
     * @return array
     */
    public function getUserTypeDescription();

    /**
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     * @param array $controlName
     *
     * @return string
     */
    public function getAdminListViewHTML(array $property, array $value, array $controlName);

    /**
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     * @param array $controlName
     *
     * @return string
     */
    public function getPropertyFieldHtml(array $property, array $value, array $controlName);

    /**
     *
     * @internal Если фильтр выбран, то получить доступ к его значению можно только через
     *     $GLOBALS[$controlName['VALUE']]
     *
     *
     * @param array $property
     * @param array $controlName
     *
     * @return string
     */
    public function getAdminFilterHTML(array $property, array $controlName);

    /**
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     *
     * @return mixed
     */
    public function convertToDB(array $property, array $value);

    /**
     * @param array $property
     * @param array $value ['VALUE' => 'mixed', 'DESCRIPTION' => 'string']
     *
     * @return mixed
     */
    public function convertFromDB(array $property, array $value);

    //TODO Обязательно объявить все возможные методы, т.к. интерфейс потом надо всегда соблюдать целиком.

}
