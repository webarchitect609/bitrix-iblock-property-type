<?php

namespace WebArch\BitrixIblockPropertyType\Abstraction;

use WebArch\BitrixIblockPropertyType\Exception\NotImplementedMethodException;

abstract class IblockPropertyTypeBase implements IblockPropertyTypeInterface
{
    /**
     * Форма редактирования свойства.
     */
    const CONTROL_MODE_EDIT_FORM = 'EDIT_FORM';

    /**
     * Форма заполнения свойства (из формы редактирования элемента).
     */
    const CONTROL_MODE_FORM_FILL = 'FORM_FILL';

    /**
     * Отображение или редактирование в административной части в списке элементов инфоблока
     */
    const CONTROL_MODE_IBLOCK_ELEMENT_ADMIN = 'iblock_element_admin';

    const PROPERTY_TYPE_STRING = 'S';

    const PROPERTY_TYPE_NUMBER = 'N';

    const PROPERTY_TYPE_LIST = 'L';

    const PROPERTY_TYPE_FILE = 'F';

    const PROPERTY_TYPE_SECTION_LINK = 'G';

    const PROPERTY_TYPE_ELEMENT_LINK = 'E';

    /**
     * @inheritdoc
     */
    public function init()
    {
        AddEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [$this, 'getUserTypeDescription']
        );
    }

    /**
     * @inheritdoc
     */
    public function getUserTypeDescription()
    {
        return array_merge(
            [
                'PROPERTY_TYPE' => $this->getPropertyType(),
                'USER_TYPE'     => $this->getUserType(),
                'DESCRIPTION'   => $this->getDescription(),
            ],
            $this->getCallbacksMapping()
        );
    }

    /**
     * Возвращает какое свойство будет базовым для хранения значений пользовательского свойства, а также для фильтрации
     * и некоторых других действий. Возможные значения:
     *
     * S - строка
     * N - число с плавающей точкой
     * L - список значений
     * F - файл
     * G - привязка к разделам
     * E - привязка к элементам
     *
     * см. константы PROPERTY_TYPE_*
     *
     * @return string
     */
    abstract public function getPropertyType();

    /**
     * Возвращает краткое описание. Будет выведено в списке выбора типа свойства при редактировании информационного
     * блока.
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Возвращает уникальный идентификатор пользовательского свойства.
     *
     * @return string
     */
    public function getUserType()
    {
        return static::class;
    }

    /**
     * Возвращает маппинг реализованных для данного типа свойства методов.
     *
     * Неуказанные методы будут заменены на стандартную реализацию из модуля инфоблоков. Если же метод указан, но не
     * имеет конкретной реализации, будет выброшено исключение NotImplementedMethodException()
     *
     * @see IblockPropertyTypeInterface::getUserTypeDescription
     *
     * @return array
     */
    abstract public function getCallbacksMapping();

    /**
     * Определяет в каком режиме отображается свойство.
     *
     * @param array $control
     *
     * @return string
     */
    protected function getControlMode(array $control)
    {
        if (!isset($control['MODE'])) {
            return '';
        }

        $possibleValues = [
            self::CONTROL_MODE_EDIT_FORM,
            self::CONTROL_MODE_FORM_FILL,
            self::CONTROL_MODE_IBLOCK_ELEMENT_ADMIN,
        ];

        if (in_array($control['MODE'], $possibleValues)) {
            return $control['MODE'];
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function checkFields(array $property, array $value)
    {
        throw new NotImplementedMethodException('checkFields', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getLength(array $property, array $value)
    {
        throw new NotImplementedMethodException('getLength', static::class);
    }

    /**
     * @inheritdoc
     */
    public function convertToDB(array $property, array $value)
    {
        throw new NotImplementedMethodException('convertToDB', static::class);
    }

    /**
     * @inheritdoc
     */
    public function convertFromDB(array $property, array $value)
    {
        throw new NotImplementedMethodException('convertFromDB', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getPropertyFieldHtml(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPropertyFieldHtml', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getAdminListViewHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getAdminListViewHTML', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getPublicViewHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPublicViewHTML', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getPublicEditHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPublicEditHTML', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getSearchContent(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getSearchContent', static::class);
    }

    /**
     * @inheritdoc
     */
    public function prepareSettings(array $property)
    {
        throw new NotImplementedMethodException('prepareSettings', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHTML(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getSettingsHTML', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getPropertyFieldHtmlMulty(array $property, array $value, array $control)
    {
        throw new NotImplementedMethodException('getPropertyFieldHtmlMulty', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getAdminFilterHTML(array $property, array $control)
    {
        throw new NotImplementedMethodException('getAdminFilterHTML', static::class);
    }

    /**
     * @inheritdoc
     */
    public function getPublicFilterHTML(array $property, array $control)
    {
        throw new NotImplementedMethodException('getPublicFilterHTML', static::class);
    }

}
