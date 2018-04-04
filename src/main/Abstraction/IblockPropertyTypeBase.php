<?php

namespace WebArch\BitrixIblockPropertyType\Abstraction;

abstract class IblockPropertyTypeBase implements IblockPropertyTypeInterface
{
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
        //TODO Возможно, придётся сокращать, если с добавлением namespace битрикс не справится
        return static::class;
    }

    /**
     * Возвращает маппинг реализованных для данного типа свойства методов. Неуказанные методы будут заменены на
     * стандартную реализацию из модуля инфоблоков.
     *
     * @see IblockPropertyTypeInterface::getUserTypeDescription
     *
     * @return array
     */
    abstract public function getCallbacksMapping();
}
