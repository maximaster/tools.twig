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

    public function getDefaultOptions(): array
    {
        return array(
            'debug' => false,
            'charset' => SITE_CHARSET,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/maximaster/tools.twig',
            'auto_reload' => isset($_GET['clear_cache']) && strtoupper($_GET['clear_cache']) == 'Y',
            'autoescape' => false,
            'extract_result' => false,
            'use_by_default' => false
        );
    }

    public function getOptions(): array
    {
        $c = Configuration::getInstance();
        $config = $c->get('maximaster');
        $twigConfig = isset($config['tools']['twig']) ? (array)$config['tools']['twig'] : array();
        $this->options = array_merge($this->getDefaultOptions(), $twigConfig);
        return $this->options;
    }

    public function asArray(): array
    {
        return $this->options;
    }

    public function getCache(): string
    {
        return (string)$this->options['cache'];
    }

    public function getDebug(): bool
    {
        return (bool)$this->options['debug'];
    }

    public function getCharset(): string
    {
        return (string)$this->options['charset'];
    }

    public function getAutoReload(): bool
    {
        return (bool)$this->options['auto_reload'];
    }

    public function getAutoescape(): bool
    {
        return (bool)$this->options['autoescape'];
    }

    public function getExtractResult(): bool
    {
        return (bool)$this->options['extract_result'];
    }

    public function getUsedByDefault(): bool
    {
        return (bool)$this->options['use_by_default'];
    }

    public function setExtractResult($value): TwigOptionsStorage
    {
        $this->options['extract_result'] = !! $value;
        return $this;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value): TwigOptionsStorage
    {
        $this->options[ $offset ] = $value;
        return $this;
    }

    public function offsetUnset($offset) {}
}
