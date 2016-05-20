<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Event;

/**
 * Class TemplateEngine. Небольшой синглтон, который позволяет в процессе работы страницы несколько раз обращаться к
 * одному и тому же рендереру страниц
 * @package Maximaster\Twig
 */
class TemplateEngine
{
    private static $instance = null;

    /**
     * Очищает весь кеш твига
     */
    public static function clearAllCache()
    {
        self::getInstance()->clearCacheFiles();
    }

    private static function getDefaultOptions()
    {
        return array(
            'debug' => false,
            'charset' => SITE_CHARSET,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',
            'auto_reload' => isset( $_GET[ 'clear_cache' ] ) && strtoupper($_GET[ 'clear_cache' ]) == 'Y',
            'autoescape' => false
        );
    }

    private static function getOptions()
    {
        $c = Configuration::getInstance();
        $config = $c->get('maximaster');
        $twigConfig = (array)$config['tools']['twig'];

        return array_merge(self::getDefaultOptions(), $twigConfig);
    }

    private static function getInstance()
    {
        if (self::$instance) return self::$instance;

        $loader = new BitrixLoader($_SERVER['DOCUMENT_ROOT']);

        $twigOptions = self::getOptions();

        $twig = new \Twig_Environment($loader, $twigOptions);

        if ($twig->isDebug())
        {
            $twig->addExtension(new \Twig_Extension_Debug());
        }

        $twig->addExtension(new BitrixExtension());
        $twig->addExtension(new CustomFunctionsExtension());

        $event = new Event('', 'onAfterTwigTemplateEngineInited', array($twig));
        $event->send();
        if ($event->getResults())
        {
            foreach($event->getResults() as $evenResult)
            {
                if($evenResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
                {
                    $twig = current($evenResult->getParameters());
                }
            }
        }

        return self::$instance = $twig;
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
    )
    {
        if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
        {
            throw new \Twig_Error('Пролог не подключен');
        }

        $component = $template->__component;

        echo self::getInstance()->render($template->__fileAlt ?: $templateFile, array(
            'result' => $arResult,
            'params' => $arParams,
            'lang' => $arLangMessages,
            'template' => $template,
            'component' => $component,
            'templateFolder' => $templateFolder,
            'parentTemplateFolder' => $parentTemplateFolder
        ));

        $component_epilog = $templateFolder . '/component_epilog.php';
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . $component_epilog))
        {
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
}