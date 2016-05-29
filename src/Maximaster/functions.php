<?php

use Maximaster\Tools\Twig\TemplateEngine;

global $arCustomTemplateEngines;
$arCustomTemplateEngines['twig'] = array(
    'templateExt' => array('twig'),
    'function'    => 'maximasterRenderTwigTemplate'
);
if (!function_exists('maximasterRenderTwigTemplate'))
{
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
}
else
{
    throw new \Twig_Error_Loader('Необходимо, чтобы функция с именем maximasterRenderTwigTemplate не была определена');
}
