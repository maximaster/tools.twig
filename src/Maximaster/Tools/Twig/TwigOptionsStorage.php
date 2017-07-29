<?php

namespace Maximaster\Tools\Twig;

use Bitrix\Main\Config\Configuration;

/**
 * Класс для более удобного способа доступа к настрокам twig
 * @package Maximaster\Tools\Twig
 */
class TwigOptionsStorage implements \ArrayAccess
{
    private $options = array();

    public function __construct()
    {
        $this->getOptions();
    }

    public function getDefaultOptions()
    {
        return array(
            'debug' => false,
            'charset' => SITE_CHARSET,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',
            'auto_reload' => isset( $_GET[ 'clear_cache' ] ) && strtoupper($_GET[ 'clear_cache' ]) == 'Y',
            'autoescape' => false
        );
    }

    public function getOptions()
    {
        $c = Configuration::getInstance();
        $config = $c->get('maximaster');
        $twigConfig = isset($config['tools']['twig']) ? (array)$config['tools']['twig'] : [];
        $this->options = array_merge($this->getDefaultOptions(), $twigConfig);
        return $this->options;
    }

    public function asArray()
    {
        return $this->options;
    }

    public function getCache()
    {
        return $this->options['cache'];
    }

    public function getDebug()
    {
        return $this->options['debug'];
    }

    public function getCharset()
    {
        return $this->options['charset'];
    }

    public function getAutoReload()
    {
        return $this->options['auto_reload'];
    }

    public function getAutoescape()
    {
        return $this->options['autoescape'];
    }

    public function offsetExists($offset)
    {
        return isset($this->options[ $offset ]);
    }

    public function offsetGet($offset)
    {
        return $this->options[ $offset ];
    }

    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        return;
    }
}
