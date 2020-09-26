<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use CBitrixComponentTemplate;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\DebugExtension as TwigDebugExtension;
use Twig\Error\Error as TwigError;
use Bitrix\Main\Localization\Loc;

/**
 * Class TemplateEngine. Небольшой синглтон, который позволяет в процессе работы страницы несколько раз обращаться к
 * одному и тому же рендереру страниц
 * @package Maximaster\Twig
 */
class TemplateEngine
{
    /**
     * @var Twig\Environment
     */
    private $engine;

    /**
     * @var TwigOptionsStorage
     */
    private $options;

    /**
     * Возвращает настроенный инстанс движка Twig
     * @return Twig\Environment
     */
    public function getEngine()
    {
        return $this->engine;
    }

    private static $instance = null;

    /**
     * Очищает весь кеш твига
     *
     * @deprecated начиная с 0.8. Будет удален в 1.0
     */
    public static function clearAllCache()
    {
        $cleaner = new TwigCacheCleaner(self::getInstance()->getEngine());
        return $cleaner->clearAll();
    }

    public function __construct()
    {
        $this->options = new TwigOptionsStorage();

        $this->engine = new TwigEnvironment(
            new BitrixLoader($_SERVER['DOCUMENT_ROOT']),
            $this->options->asArray()
        );

        $this->initExtensions();
        $this->generateInitEvent();

        self::$instance = $this;
    }

    /**
     * Инициализируется расширения, необходимые для работы
     */
    private function initExtensions()
    {
        if ($this->engine->isDebug()) {
            $this->engine->addExtension(new TwigDebugExtension());
        }

        $this->engine->addExtension(new BitrixExtension());
        $this->engine->addExtension(new PhpGlobalsExtension());
        $this->engine->addExtension(new CustomFunctionsExtension());
    }

    /**
     * Создается событие для внесения в Twig изменения из проекта
     */
    private function generateInitEvent()
    {
        $eventName = 'onAfterTwigTemplateEngineInited';
        $event = new Event('', $eventName, array($this->engine));
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $evenResult) {
                if ($evenResult->getType() == EventResult::SUCCESS) {
                    $twig = current($evenResult->getParameters());
                    if (!($twig instanceof TwigEnvironment)) {
                        throw new \LogicException(
                            "Событие '{$eventName}' должно возвращать экземпляр класса ".
                            "'\\TwigEnvironment' при успешной отработке"
                        );
                    }

                    $this->engine = $twig;
                }
            }
        }
    }

    public static function getInstance()
    {
        return self::$instance ?: (self::$instance = new self);
    }

    /**
     * Собственно сама функция - рендерер. Принимает все данные о шаблоне и компоненте, выводит в stdout данные.
     * Содержит дополнительную обработку для component_epilog.php
     *
     * @param string $templateFile
     * @param array $arResult
     * @param array $arParams
     * @param array $arLangMessages
     * @param string $templateFolder
     * @param string $parentTemplateFolder
     * @param CBitrixComponentTemplate $template
     * @throws Twig\Error\Error
     */
    public static function render(
        /** @noinspection PhpUnusedParameterInspection */ $templateFile,
        $arResult,
        $arParams,
        $arLangMessages,
        $templateFolder,
        $parentTemplateFolder,
        CBitrixComponentTemplate $template
    ) {
        if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
            throw new TwigError('Пролог не подключен');
        }

        $component = $template->__component;
        /** @var BitrixLoader $loader */
        $loader = self::getInstance()->getEngine()->getLoader();
        if (!($loader instanceof BitrixLoader)) {
            throw new \LogicException(
                "Загрузчиком должен быть 'Maximaster\\Tools\\Twig\\BitrixLoader' или его наследник"
            );
        }

        $templateName = $loader->makeComponentTemplateName($template);

        $engine = self::getInstance();
        $options = $engine->getOptions();

        if ($options['extract_result']) {
            $context = $arResult;
            $context['result'] =& $arResult;
        } else {
            $context = array('result' => $arResult);
        }

        // Битрикс не умеет "лениво" грузить языковые сообщения если они запрашиваются из twig, т.к. ищет вызов
        // GetMessage, а после ищет рядом lang-папки. Т.к. рядом с кешем их конечно нет
        // Кроме того, Битрикс ждёт такое же имя файла, внутри lang-папки. Т.е. например template.twig
        // Но сам includ'ит их, что в случае twig файла конечно никак не сработает. Поэтому подменяем имя
        $templateMess = Loc::loadLanguageFile(
            $_SERVER['DOCUMENT_ROOT'].preg_replace('/[.]twig$/', '.php', $template->GetFile())
        );

        // Это не обязательно делать если не используется lang, т.к. Битрикс загруженные фразы все равно запомнил
        // и они будут доступны через вызов getMessage в шаблоне. После удаления lang, можно удалить и этот код
        if (is_array($templateMess)) {
            $arLangMessages = array_merge($arLangMessages, $templateMess);
        }

        $context = array(
            'params' => $arParams,
            'lang' => $arLangMessages,
            'template' => $template,
            'component' => $component,
            'templateFolder' => $templateFolder,
            'parentTemplateFolder' => $parentTemplateFolder,
            'render' => compact('templateName', 'engine'),
        ) + $context;

        echo self::getInstance()->getEngine()->render($templateName, $context);

        $component_epilog = $templateFolder . '/component_epilog.php';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $component_epilog)) {
            /** @var \CBitrixComponent $component */
            $component->SetTemplateEpilog(array(
                'epilogFile' => $component_epilog,
                'templateName' => $template->__name,
                'templateFile' => $template->__file,
                'templateFolder' => $template->__folder,
                'templateData' => false,
            ));
        }
    }

    /**
     * Рендерит произвольный twig-файл, возвращает результат в виде строки
     * @param string $src Путь к twig-файлу
     * @param array $context Контекст
     * @return string Результат рендера
     */
    public static function renderStandalone($src, $context = array())
    {
        return self::getInstance()->getEngine()->render($src, $context);
    }

    /**
     * Рендерит произвольный twig-файл, выводит результат в stdout
     * @param string $src
     * @param array $context
     */
    public static function displayStandalone($src, $context = array())
    {
        echo self::renderStandalone($src, $context);
    }

    public function getOptions()
    {
        return $this->options;
    }
}