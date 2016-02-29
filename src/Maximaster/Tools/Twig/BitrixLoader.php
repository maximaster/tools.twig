<?php

namespace Maximaster\Tools\Twig;

/**
 * Class BitrixLoader. Класс загрузчик файлов шаблонов. Понимает специализированный синтаксис
 * @package Maximaster\Twig
 */
class BitrixLoader extends \Twig_Loader_Filesystem implements \Twig_LoaderInterface
{
    /**
     * Принимает на вход имя компонента и шаблона в виде<br>
     * <b>vendor:componentname[:template[:specifictemplatefile]]</b><br>
     * Например bitrix:news.list:.default, или bitrix:sale.order:show:step1
     *
     * @inheritdoc
     * @param string $name
     */
    function getSource($name)
    {
		return file_get_contents($this->getSourcePath($name));
    }

	function getSourcePath($name)
	{
		/*if ($name == SITE_TEMPLATE_ID)
		{
			return $this->siteTemplate();
		}*/

		$realFileName = $_SERVER[ 'DOCUMENT_ROOT' ] . $name;
		if (file_exists($realFileName))
			return $realFileName;

		return $this->getComponentTemplatePath($name);
	}

    function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Не использовать в продакшене!!
     * Метод используется только в режиме разработки или при использовании опции auto_reload = true
	 * @param string $name Путь к шаблону
	 * @param int $time Время изменения закешированного шаблона
	 * @return bool Актуален ли закешированный шаблон
     */
    function isFresh($name, $time)
    {
        return filemtime($this->getSourcePath($name)) <= $time;
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
        list($namespace, $component, $template, $file) = explode(':', $name);

        if (strlen($template) === 0)
        {
            $template = '';
        }

        if (strlen($file) === 0)
        {
            $file = '';
        }

        $componentName = "{$namespace}:{$component}";

        $component = new \CBitrixComponent();
        $component->InitComponent($componentName, $template);
		$component->__templatePage = $file;

        $obTemplate = new \CBitrixComponentTemplate();
        $obTemplate->Init($component);
        $templatePath = $_SERVER['DOCUMENT_ROOT'] . $obTemplate->GetFile();

        if (!file_exists($templatePath))
        {
            throw new \Twig_Error_Loader("Не удалось найти шаблон '{$name}'");
        }

        return $templatePath;
    }

    /*
    private function siteTemplate()
    {
        $headerFile = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/header.twig';
        $footerFile = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/footer.twig';

        if (!file_exists($headerFile) || !file_exists($footerFile))
        {
            throw new \Twig_Error_Loader("Не удалось найти шаблон '" . SITE_TEMPLATE_ID . "'");
        }

        return file_get_contents($headerFile) . '{% block workarea %}{% endblock %}' . file_get_contents($footerFile);

    }
    */
}