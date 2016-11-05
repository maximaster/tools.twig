## Расширение 

С версии 0.5 появилась возможность добавления собственных расширений. Реализуется это с помощью обработчика события **onAfterTwigTemplateEngineInited**. Событие не привязано ни к одному из модулей, поэтому при регистрации события в качестве идентификатора модуля нужно указать пустую строку.
В событие передается объект `\Twig_Environment`, с которым можно сделать определенные манипуляции.
Пример обработчика события, который зарегистрирует свое расширение:

```php

use Bitrix\Main\EventResult;

AddEventHandler('', 'onAfterTwigTemplateEngineInited', array('onAfterTwigTemplateEngineInited', 'addTwigExtension'));

class onAfterTwigTemplateEngineCreated
{
    public static function addTwigExtension(\Twig_Environment $engine)
    {
        $engine->addExtension(new MySuperDuperExtension());
        return new EventResult(EventResult::SUCCESS, array($engine));
    }
}
```


Здесь класс MySuperDuperExtension должен быть наследником класса `\Twig_Extension` или имплементацией интерфейса `\Twig_ExtensionInterface`.