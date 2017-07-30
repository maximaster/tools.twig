<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use CBitrixComponentTemplate;
use Twig_Environment;

/**
 * Class TemplateEngine. Небольшой синглтон, который позволяет в процессе работы страницы несколько раз обращаться к
 * одному и тому же рендереру страниц
 * @package Maximaster\Twig
 */
class TemplateEngine
{
    /**
     * @var Twig_Environment
     */
    private $engine;

    /**
     * @var TwigOptionsStorage
     */
    private $options;

    /**
     * Возвращает настроенный инстанс движка Twig
     * @return Twig_Environment
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

        $this->engine = new Twig_Environment(
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
            $this->engine->addExtension(new \Twig_Extension_Debug());
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
                    if (!($twig instanceof Twig_Environment)) {
                        throw new \LogicException(
                            "Событие '{$eventName}' должно возвращать экземпляр класса ".
                            "'\\Twig_Environment' при успешной отработке"
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
     * @throws \Twig_Error
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
            throw new \Twig_Error('Пролог не подключен');
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