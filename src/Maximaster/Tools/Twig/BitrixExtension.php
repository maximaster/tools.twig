<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Application;
use Twig\Extension\AbstractExtension as TwigAbstractExtension;
use Twig\Extension\GlobalsInterface as TwigGlobalsInterface;
use Twig\TwigFunction;

/**
 * Class BitrixExtension. Расширение, которое позволяет в шаблонах использовать типичные для битрикса конструкции
 *
 * @package Maximaster\Twig
 */
class BitrixExtension extends TwigAbstractExtension implements TwigGlobalsInterface
{
    private $isD7 = null;

    public function getName()
    {
        return 'bitrix_extension';
    }

    public function getGlobals(): array
    {
        global $APPLICATION, $USER;

        $coreVariables = [
            'APPLICATION'        => $APPLICATION,
            'USER'               => $USER,
            'SITE_TEMPLATE_PATH' => SITE_TEMPLATE_PATH,
        ];

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
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('showError', 'ShowError'),
            new TwigFunction('showMessage', 'ShowMessage'),
            new TwigFunction('showNote', 'ShowNote'),
            new TwigFunction('bitrix_sessid_post', 'bitrix_sessid_post'),
            new TwigFunction('bitrix_sessid_get', 'bitrix_sessid_get'),
            new TwigFunction('getMessage', $this->isD7() ? '\\Bitrix\\Main\\Localization\\Loc::getMessage' : 'GetMessage'),
            new TwigFunction('showComponent', array(__CLASS__, 'showComponent')),
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