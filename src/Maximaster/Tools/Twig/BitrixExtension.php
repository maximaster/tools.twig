<?php

namespace Maximaster\Tools\Twig;

/**
 * Class BitrixExtension. Расширение, которое позволяет в шаблонах использовать типичные для битрикса конструкции
 *
 * @package Maximaster\Twig
 */
class BitrixExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'bitrix_extension';
    }

    public function getGlobals()
    {
        global $APPLICATION, $USER;

        return array(
            'APPLICATION'   => $APPLICATION,
            'USER'          => $USER,
            '_SERVER'       => $_SERVER,
            '_REQUEST'      => $_REQUEST,
            '_GET'          => $_GET,
            '_POST'         => $_POST,
            '_FILES'        => $_FILES,
            'SITE_ID'       => SITE_ID,
            'LANG'          => LANG,
        );
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('showError', 'ShowError'),
            new \Twig_SimpleFunction('showMessage', 'ShowMessage'),
            new \Twig_SimpleFunction('showNote', 'ShowNote'),
            new \Twig_SimpleFunction('bitrix_sessid_post', 'bitrix_sessid_post'),
            new \Twig_SimpleFunction('bitrix_sessid_get', 'bitrix_sessid_get'),
            new \Twig_SimpleFunction('getMessage', 'GetMessage'),
            new \Twig_SimpleFunction('showComponent', array(__CLASS__, 'showComponent')),
        );
    }

    /**
     * @param string $componentName
     * @param string $componentTemplate
     * @param array $arParams
     * @param \CBitrixComponent $parentComponent
     * @param array $arFunctionParams
     */
    public static function showComponent($componentName, $componentTemplate, $arParams = array(), $parentComponent = null, $arFunctionParams = array())
    {
        global $APPLICATION;
        $APPLICATION->IncludeComponent($componentName, $componentTemplate, $arParams, $parentComponent, $arFunctionParams);
    }
}