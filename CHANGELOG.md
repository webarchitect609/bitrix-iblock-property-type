Change Log
==========

2.0.0
-----

### BREAKING CHANGE:

- `php: ^7.2` вместо `>=5.5`

### Добавлено:

- новый тип свойства "Справочник(с сортировкой множества)", но пока с ограничением на 1000 элементов;
- типизация всех исключений библиотеки через `BitrixIblockPropertyTypeExceptionInterface`.

### Исправлено:

- исправлены названия аргументов `IblockPropertyTypeInterface::getSettingsHTML()`.