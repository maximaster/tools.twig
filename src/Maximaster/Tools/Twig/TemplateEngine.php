<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Event;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use CBitrixComponentTemplate;

/**
 * Class TemplateEngine. Небольшой синглтон, который позволяет в процессе работы страницы несколько раз обращаться к
 * одному и тому же рендереру страниц
 * @package Maximaster\Twig
 */
class TemplateEngine
{
    /**
     * @var \Twig_Environment
     */
    private $engine;

    /**
     * @var CBitrixComponentTemplate
     */
    private $lastTemplate;

    /**
     * Возвращает настроенный инстанс движка Twig
     * @return TwigEnvironment
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
        $optionsStorage = new TwigOptionsStorage();
        $this->engine = new TwigEnvironment(
            new BitrixLoader($_SERVER['DOCUMENT_ROOT']),
            $optionsStorage->asArray()
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
                if ($evenResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $twig = current($evenResult->getParameters());
                    if (!($twig instanceof TwigEnvironment)) {
                        throw new \LogicException(
                            "Событие '{$eventName}' должно возвращать экземпляр класса ".
                            "'\Maximaster\Tools\Twig\TwigEnvironment' при успешной отработке"
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
     * @param \CBitrixComponentTemplate $template
     * @throws \Twig_Error
     */
    public static function render(
        $templateFile,
        $arResult,
        $arParams,
        $arLangMessages,
        $templateFolder,
        $parentTemplateFolder,
        $template
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

        self::getInstance()->lastTemplate = $template;
        $templateName = $loader->makeComponentTemplateName($template);
        echo self::getInstance()->getEngine()->render($templateName, array(
            'result' => $arResult,
            'params' => $arParams,
            'lang' => $arLangMessages,
            'template' => $template,
            'component' => $component,
            'templateFolder' => $templateFolder,
            'parentTemplateFolder' => $parentTemplateFolder
        ));

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
     * Возвращает последний загруженный шаблон
     * @return CBitrixComponentTemplate
     */
    public function getLastTemplate()
    {
        return $this->lastTemplate;
    }
}