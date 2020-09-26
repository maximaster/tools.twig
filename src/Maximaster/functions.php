<?php

use Maximaster\Tools\Twig\TemplateEngine;
use Maximaster\Tools\Twig\Aop\AspectKernel;
use Twig\Error\LoaderError as TwigLoaderError;

if (!function_exists('maximasterRenderTwigTemplate')) {
    function maximasterRenderTwigTemplate(
        $templateFile,
        $arResult,
        $arParams,
        $arLangMessages,
        $templateFolder,
        $parentTemplateFolder,
        \CBitrixComponentTemplate $template)
    {
        TemplateEngine::render(
            $templateFile,
            $arResult,
            $arParams,
            $arLangMessages,
            $templateFolder,
            $parentTemplateFolder,
            $template
        );
    }

    function maximasterRegisterTwigTemplateEngine()
    {
        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['twig'] = array(
            'templateExt' => array('twig'),
            'function'    => 'maximasterRenderTwigTemplate'
        );
    }

    maximasterRegisterTwigTemplateEngine();

    if (class_exists('\Go\Core\AspectKernel', true) && class_exists('CMain')) {
        $aspectKernel = AspectKernel::getInstance();
        $aspectKernel->init(array(
            'appDir' => $_SERVER['DOCUMENT_ROOT'],
            'cacheDir' => TemplateEngine::getInstance()->getOptions()->getCache(),
        ));
    }
} else {
    throw new TwigLoaderError('Необходимо, чтобы функция с именем maximasterRenderTwigTemplate не была определена');
}
