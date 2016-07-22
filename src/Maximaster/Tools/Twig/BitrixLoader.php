<?php

namespace Maximaster\Tools\Twig;

/**
 * Class BitrixLoader. Класс загрузчик файлов шаблонов. Понимает специализированный синтаксис
 * @package Maximaster\Twig
 */
class BitrixLoader extends \Twig_Loader_Filesystem implements \Twig_LoaderInterface
{
    /** @var array Статическое хранилище для уже отрезолвленных путей для ускорения */
    private static $resolved = array();
    /** @var array Статическое хранилище нормализованных имен шаблонов для ускорения */
    private static $normalized = array();

    /**
     * {@inheritdoc}
     *
     * Принимает на вход имя компонента и шаблона в виде<br>
     * <b>vendor:componentname[:template[:specifictemplatefile]]</b><br>
     * Например bitrix:news.list:.default, или bitrix:sale.order:show:step1
     *
     * @param string $name
     */
    public function getSource($name)
    {
        return file_get_contents($this->getSourcePath($name));
    }

    /** {@inheritdoc} */
    public function getCacheKey($name)
    {
        return $this->normalizeName($name);
    }

    /**
     * {@inheritdoc}
     * Не использовать в продакшене!!
     * Метод используется только в режиме разработки или при использовании опции auto_reload = true
     * @param string $name Путь к шаблону
     * @param int    $time Время изменения закешированного шаблона
     * @return bool Актуален ли закешированный шаблон
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->getSourcePath($name)) <= $time;
    }

    /**
     * Получает путь до файла с шаблоном по его имени
     *
     * @param string $name
     * @return string
     * @throws \Twig_Error_Loader
     */
    public function getSourcePath($name)
    {
        $name = $this->normalizeName($name);
        
        if (isset(static::$resolved[ $name ])) {
            return static::$resolved[ $name ];
        }

        $resolved = '';
        $realFileName = $_SERVER['DOCUMENT_ROOT'] . $name;
        if (file_exists($realFileName)) {
            $resolved = $realFileName;
        } else {
            $resolved = $this->getComponentTemplatePath($name);
        }

        static::$resolved[ $name ] = $resolved;

        return $resolved;

    }


    /**
     * По Битрикс-имени шаблона возвращает путь к его файлу
     *
     * @param string $name
     * @return string
     * @throws \Twig_Error_Loader
     */
    private function getComponentTemplatePath($name)
    {
        $name = $this->normalizeName($name);

        list($namespace, $component, $template, $file) = explode(':', $name);

        $componentName = "{$namespace}:{$component}";

        $component = new \CBitrixComponent();
        $component->InitComponent($componentName, $template);
        $component->__templatePage = $file;

        $obTemplate = new \CBitrixComponentTemplate();
        $obTemplate->Init($component);
        $templatePath = $_SERVER['DOCUMENT_ROOT'] . $obTemplate->GetFile();

        if (!file_exists($templatePath)) {
            throw new \Twig_Error_Loader("Не удалось найти шаблон '{$name}'");
        }

        return $templatePath;
    }

    /**
     * На основании шаблона компонента создает полное имя для Twig
     *
     * @param \CBitrixComponentTemplate $template
     * @return string
     */
    public function makeComponentTemplateName(\CBitrixComponentTemplate $template)
    {
        if ($template->__fileAlt) {
            return $template->__fileAlt;
        }

        $templatePage = $template->__page;
        $templateName = $template->__name;
        $componentName = $template->__component->getName();

        return "{$componentName}:{$templateName}:{$templatePage}";
    }

    /**
     * Преобразует имя в максимально-полное начертание
     *
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        if (strpos($name, '/') !== false) {
            return parent::normalizeName($name);
        }

        if (isset(static::$normalized[ $name ])) {
            return static::$normalized[ $name ];
        }

        //Убираем все повторяющиеся двоеточия
        $name = preg_replace('#/{2,}#', ':', (string)$name);

        list( $namespace, $component, $template, $file ) = explode(':', $name);

        if (strlen($template) === 0) {
            $template = '.default';
        }

        if (strlen($file) === 0) {
            $file = 'template';
        }

        $normalizedName = "{$namespace}:{$component}:{$template}:{$file}";
        static::$normalized[ $name ] = $normalizedName;
        return $normalizedName;

    }
}