<?php

use Maximaster\Tools\Twig\TemplateEngine;

global $arCustomTemplateEngines;
$arCustomTemplateEngines['twig'] = array(
    'templateExt' => array('twig'),
    'function'    => 'maximasterRnderTwigTemplate'
);
if (!function_exists('maximasterRnderTwigTemplate'))
{
    function maximasterRnderTwigTemplate(
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
    throw new \Twig_Error_Loader('Необходимо, чтобы функия с именем maximasterRnderTwigTemplate не была определена');
}
