<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Application;

/**
 * Class BitrixExtension. Расширение, которое позволяет в шаблонах использовать типичные для битрикса конструкции
 *
 * @package Maximaster\Twig
 */
class BitrixExtension extends \Twig_Extension
{
    private $isD7 = null;

    public function getName()
    {
        return 'bitrix_extension';
    }

    public function getGlobals()
    {
        global $APPLICATION, $USER;

        $coreVariables = array(
            'APPLICATION'   => $APPLICATION,
            'USER'          => $USER,
        );

        if ($this->isD7()) {
            $coreVariables['app'] = Application::getInstance();
        }

        return $coreVariables;

    }

    private function isD7()
    {
        if ($this->isD7 === null) {
            $this->isD7 = class_exists('\Bitrix\Main\Application');
        }

        return $this->isD7;
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
            new \Twig_SimpleFunction('getMessage', $this->isD7() ? '\Bitrix\Main\Loc::getMessage' : 'GetMessage'),
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