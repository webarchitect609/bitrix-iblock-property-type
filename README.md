Набор собственных типов свойств элементов инфоблоков, а также базовый функционал, призванный максимально упростить и 
ускорить разработку новых собственных типов свойств. 

Если вы хотите [создавать свои пользовательские типы свойств, то вам нужен пакет webarchitect609/bitrix-user-type](https://packagist.org/packages/webarchitect609/bitrix-user-type)


Как использовать: 
-----------------

1 Установить через composer 

`composer require webarchitect609/bitrix-iblock-property-type`

2 В init.php инициализировать используемые типы свойств. Например, 

`(new \WebArch\BitrixIblockPropertyType\YesNoType())->init();`

3 Теперь можно настраивать инфоблок, добавив свойство нового типа! 

Как разработать свой тип свойства: 
----------------------------------

1 Наследовать свой тип от базовой реализации `\WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase`, 

2 Определить обязательные методы `getPropertyType()` и `getDescription()`

3 Переопределить необходимые для вашего типа свойства методы, описанные интерфейсом 
`\WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeInterface` 

4 Переопределённые в пункте 3 методы должны быть включены в определение ещё одного обязательного метода 
`getCallbacksMapping();`

5 Инициализировать свой тип свойства в init.php

`(new MyIblockPropertyType())->init();`

Теперь можно настраивать инфоблок, добавив свойство нового типа! 
