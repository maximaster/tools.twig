## Управление настройками

Библиотека конфигурируется с помощью файла /bitrix/.settings.php (или /bitrix/.settings_extra.php). Нужно завести в этом файле опцию maximaster, и оперировать значением tools->twig. Ниже описаны значения опций, которые заданы библиотекой по-умолчанию:

```php

//...
'maximaster' => array(
    'value' => array(
        'tools' => array(
            'twig' => array(
				// Режим отладки выключен
				'debug' => false,

				//Кодировка соответствует кодировке продукта
				'charset' => SITE_CHARSET,

				//кеш хранится в уникальной директории. Должен быть полный абсолютный путь
				'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',

				//Автообновление включается только в момент очистки кеша
				'auto_reload' => isset( $_GET[ 'clear_cache' ] ) && strtoupper($_GET[ 'clear_cache' ]) == 'Y',

				//Автоэскейп отключен, т.к. битрикс по-умолчанию его сам делает
				'autoescape' => false,
            )
        )
    )
)
//...

```
При выборе значений для конфигов нужно опираться на [документацию twig по настройкам Twig_Environment](http://twig.sensiolabs.org/doc/api.html#environment-options). Поддерживаются все возможные согласно этой документации опции
