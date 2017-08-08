<?php

namespace Maximaster\Tools\Twig\Aop;

use Go\Core\AspectContainer;
use Maximaster\Tools\Twig\Aop\Aspect\FixAjaxComponentAspect;

/**
 * Class AspectKernel
 * @package Maximaster\Tools\Twig\Aop
 */
class AspectKernel extends \Go\Core\AspectKernel
{
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new FixAjaxComponentAspect);
    }
}
