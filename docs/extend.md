## Расширение 

С версии 0.5 появилась возможность добавления собственных расширений. Реализуется это с помощью обработчика события **onAfterTwigTemplateEngineInited**. Событие не привязано ни к одному из модулей, поэтому при регистрации события в качестве идентификатора модуля нужно указать пустую строку.
В событие передается объект `Twig\Environment`, с которым можно сделать определенные манипуляции.
Пример обработчика события, который зарегистрирует свое расширение:

```php

use Bitrix\Main\EventResult;
use Twig\Environment;

AddEventHandler('', 'onAfterTwigTemplateEngineInited', array('EventClass', 'addTwigExtension'));

class EventClass
{
    public static function addTwigExtension(Environment $engine): EventResult
    {
        $engine->addExtension(new MySuperDuperExtension());
        return new EventResult(EventResult::SUCCESS, array($engine));
    }
}
```
Пример на D7:
```php

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;
use Twig\Environment;

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('', 'onAfterTwigTemplateEngineInited', array('EventClass', 'addTwigExtension'));

class EventClass
{
    public static function addTwigExtension(Event $event): EventResult
    {
        /* @var Environment $engine */
        $engine = $event->getParameter('engine');
        $engine->addExtension(new MySuperDuperExtension());
        return new EventResult(EventResult::SUCCESS, array($engine));
    }
}
```

Здесь класс MySuperDuperExtension должен быть наследником класса `Twig\Extension\AbstractExtension` или имплементацией интерфейса `Twig\Extension\GlobalsInterface`.