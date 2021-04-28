<?php

namespace Maximaster\Tools\Twig\Aop\Aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use CComponentAjax;
use CAjax;

class FixAjaxComponentAspect implements Aspect
{
    /**
     * @param MethodInvocation $invocation
     * @Around("execution(public CComponentAjax->CheckSession(*))")
     */
    public function aroundCheckSession(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        if ($result) {
            return $result;
        }

        /**
         * @var CComponentAjax $component
         */
        $component = $invocation->getThis();

        $component->componentID = $this->getComponentId($component->componentName, $component->componentTemplate, $component->arParams['AJAX_OPTION_ADDITIONAL']);

        if (!$component->componentID) {
            return false;
        }

        if ($current_session = CAjax::GetSession())
        {
            if ($component->componentID == $current_session)
            {
                $component->bAjaxSession = true;
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    protected function getComponentId($componentName, $componentTemplate, $additionalID)
    {
        $foundTrace = false;
        foreach (debug_backtrace() as $trace) {
            if ($trace['class'] == 'Twig\\Template') {
                $foundTrace = $trace;
                break;
            }
        }

        if (!$foundTrace) {
            return false;
        }

        return md5(implode('|', array(
            $foundTrace['file'],
            $foundTrace['line'],
            $componentName,
            $componentTemplate ? strlen($componentName) : '.default',
            $additionalID
        )));
    }
}
