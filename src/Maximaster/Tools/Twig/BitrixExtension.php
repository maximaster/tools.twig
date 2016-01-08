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
        return [
            new \Twig_SimpleFunction('showError', 'ShowError'),
            new \Twig_SimpleFunction('showMessage', 'ShowMessage'),
            new \Twig_SimpleFunction('showNote', 'ShowNote'),
            new \Twig_SimpleFunction('bitrix_sessid_post', 'bitrix_sessid_post'),
            new \Twig_SimpleFunction('bitrix_sessid_get', 'bitrix_sessid_get'),
            new \Twig_SimpleFunction('getMessage', 'GetMessage'),
        ];
    }
}