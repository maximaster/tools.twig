<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Config\Configuration;

/**
 * Class TemplateEngine. Небольшой синглтон, который позволяет в процессе работы страницы несколько раз обращаться к
 * одному и тому же рендереру страниц
 * @package Maximaster\Twig
 */
class TemplateEngine
{
    private static $instance = null;

    private static function getInstance()
    {
        if (self::$instance) return self::$instance;

        //\Twig_Autoloader::register();
        $loader = new BitrixLoader($_SERVER['DOCUMENT_ROOT']);

        $c = Configuration::getInstance();
        $config = $c->get('maximaster');
        $twigConfig = $config['tools']['twig'];

        $defaultCachePath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig';
        $cachePath = isset($twigConfig['cache']) ? $twigConfig['cache'] : $defaultCachePath;
        $autoescape = isset($twigConfig['autoescape']) ? $twigConfig['autoescape'] : false;
        $isDebug = $twigConfig['debug'] === true ? true : false;

        $twigOptions = array(
            'cache' => $cachePath,
            'charset' => SITE_CHARSET,
            'autoescape' => $autoescape,
            'debug' => $isDebug
        );

        $twig = new \Twig_Environment($loader, $twigOptions);

        if ($isDebug)
        {
            $twig->addExtension(new \Twig_Extension_Debug());
        }

        $twig->addExtension(new BitrixExtension());

        return self::$instance = $twig;
    }

    /**
     * Собственно сама функция - рендерер. Принимает все данные о шаблоне и компоненте, выводит в stdout данные.
     * Содержит дополнительную обработку для component_epilog.php
     *
     * @param $templateFile
     * @param $arResult
     * @param $arParams
     * @param $arLangMessages
     * @param $templateFolder
     * @param $parentTemplateFolder
     * @param $template
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

        echo self::getInstance()->render($templateFile, array(
            'result' => $arResult,
            'params' => $arParams,
            'lang' => $arLangMessages,
            'template' => $template,
            'templateFolder' => $templateFolder,
            'parentTemplateFolder' => $parentTemplateFolder
        ));

        $component_epilog = $templateFolder . '/component_epilog.php';
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . $component_epilog))
        {
            $component = $template->__component;
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