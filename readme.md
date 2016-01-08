# maximaster/tools.twig

Данная библиотека позволяет использовать twig шаблоны в 1С Битрикс. После установки необходимо провести дополнительные манипуляции. Битрикс позволяет подключать сторонние шаблонизаторы, но для этого нужно определить глобальную переменную и глобальную функцию. Чтобы twig считывал файлы с расширением .twig, необходимо в init.php вставить примерно следующий код:

```php
//Определяем соответствие между расширением .twig и функцией рендеринга
global $arCustomTemplateEngines;
$arCustomTemplateEngines['twig'] = [
    'templateExt' => ['twig'],
    'function'    => 'renderTwigTemplate'
];

//Определяем глобальную функцию-рендерер
function renderTwigTemplate(
    $templateFile,
    $arResult,
    $arParams,
    $arLangMessages,
    $templateFolder,
    $parentTemplateFolder,
    \CBitrixComponentTemplate $template)
{
    //Передаем управление библиотеке
    \Maximaster\Twig\TemplateEngine::render(
        $templateFile,
        $arResult,
        $arParams,
        $arLangMessages,
        $templateFolder,
        $parentTemplateFolder,
        $template
    );
}
```

Вот и все. Теперь, если создать в директории шаблона компонента файл template.twig, то именно он будет использоваться при генерации шаблона.
К сожалению, ядро битрикса так устроено, что при наличии двух файлов .php и .twig, будет использовать только .php, поэтому для использования twig шаблона нужно позаботиться о том, чтобы php шаблон отсутствовал.

## Наследование шаблонов

Чтобы воспользоваться наследованием шаблонов, можно писать в extends абсолютный путь до шаблона. 
Для упрощения наследования в библиотеке присутсвует загрузчик, который позволяет по короткому имени шаблона получить к нему доступ. Синтаксис простой:

vendor:componentname[:template[:specifictemplatefile]]

Здесь
* **vendor** - это пространство имен разработчика, например bitrix или maximaster
* **componentname** - имя компонента, шаблон которого наследуется
* **template** - имя шаблона, который нужно унаследовать. Необязательный, по-умолчанию .default
* **specifictemplatefile** - конкретный файл шаблона (без расширения). Необязательный, по-умолчанию template

Например, вы хотите унаследовать шаблон new-year компонента maximaster:product. Для этого в шаблоне twig нужно написать 

```twig
{% extends "maximaster:product:new-year" %}
```

## Управление настройками

Библиотека конфигурируется с помощью файла /bitrix/.settings.php. Нужно завести в этом файле опцию maximaster, и оперировать значением tools->twig. Ниже описаны все возможные конфиги с их дефолтными значениями:

```php

//...
'maximaster' => array(
    'value' => array(
        'tools' => array(
            'twig' => array(
                'debug' => false,
                'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',
                'autoescape' => false
            )
        )
    )
)
//...

```
При выборе значений для конфигов нужно опираться на [документацию twig по настройкам Twig_Environment](http://twig.sensiolabs.org/doc/api.html#environment-options) 