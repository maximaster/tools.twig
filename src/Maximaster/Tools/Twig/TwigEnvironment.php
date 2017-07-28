<?php

namespace Maximaster\Tools\Twig;

use Twig_Environment;

class TwigEnvironment extends Twig_Environment
{
    /**
     * @var string Последний шаблон который отрисовывали (может быть вызван через include в том числе)
     */
    protected $lastRendered;

    public function render($name, array $context = array())
    {
        $this->lastRendered = $name;
        return parent::render($name, $context);
    }

    public function getLastRendered()
    {
        return $this->lastRendered;
    }
}
