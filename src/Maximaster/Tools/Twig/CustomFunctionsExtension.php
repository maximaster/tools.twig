<?php

namespace Maximaster\Tools\Twig;

use Twig\Extension\AbstractExtension as TwigAbstractExtension;
use Twig\TwigFunction;

class CustomFunctionsExtension extends TwigAbstractExtension
{
    public function getName()
    {
        return 'maximaster_functions_extension';
    }

    public function getGlobals()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('russianPluralForm', array($this, 'russianPluralForm')),
        );
    }

    /**
     * Выводит правильный вариант множественной формы числа
     *
     * @param int $howmuch Число, для которого нужно сформировать множественную форму (число будет приведено к целому)
     * @param string[] $input Массив, содержащий 3 слова ['билетов', 'билет', 'билета'] (Ноль билетов, Один билет, Два билета)
     * @return string
     */
    public static function russianPluralForm($howmuch, array $input)
    {
        $howmuch = (int)$howmuch;
        $l2 = substr($howmuch, -2);
        $l1 = substr($howmuch, -1);
        if ($l2 > 10 && $l2 < 20) {
            return $input[0];
        } else {
            switch ($l1) {
                case 0:
                    return $input[0];
                    break;
                case 1:
                    return $input[1];
                    break;
                case 2:
                case 3:
                case 4:
                    return $input[2];
                    break;
                default:
                    return $input[0];
                    break;
            }
        }
    }
}