# maximaster/tools.twig

Форк библиотеки [maximaster/tools.twig](https://github.com/maximaster/tools.twig). Подключен Twig версии 3.X. Минимальная версия PHP поднята до >=7.2.5. Можно использовать с Bitrix версии >=20.5.393, т.к. данные версии не ругаются на mbstring.func_overload = 0.

Данная библиотека позволяет использовать twig шаблоны в 1С Битрикс для компонентов 2.0. Обрабатываются файлы шаблонов, имеющие расширение `.twig`. Если создать в директории шаблона компонента файл `template.twig`, то именно он будет использоваться при генерации шаблона.

Для установки форкнутой версии через composer необходимо добавить в composer.json:

```
...
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/lozunoff/tools.twig"
        }
    ],
    "require": {
        ...
        "maximaster/tools.twig": "dev-master",
        ...
    },
...
```

и выполнить

```
composer update
```

## Простой пример

Для наследования шаблона `new_year` компонента `bitrix:news.detail` в twig шаблоне нужно всего-лишь подключить этот шаблон с помощью особого синтаксиса:

```twig
{% extends "bitrix:news.detail:new_year" %}
```
После чего можно будет переопределить все блоки, которые есть в родительском шаблоне. Подробнее о [синтаксисе](docs/syntax.md) - в документации

## Документация 

* **[Синтаксис подключения шаблонов](docs/syntax.md)**
* **[Доступные переменные и функции внутри шаблонов](docs/twig_extension.md)**
* **[Конфигурирование](docs/configuration.md)**
* **[Работа с кешем](docs/working_with_cache.md)**
* **[Расширение возможностей](docs/extend.md)**
* **[Тонкости интеграции с битриксом](docs/bitrix_pitfalls.md)**