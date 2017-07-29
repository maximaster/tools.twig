<?php

namespace Maximaster\Tools\Twig\Test;

use Exception;
use PHPUnit_Framework_TestCase;

class RenderTest extends PHPUnit_Framework_TestCase
{
    const TEST_VENDOR_NAME = '__phpUnit_maximaster';

    protected function setUp()
    {
        $_SERVER['DOCUMENT_ROOT'] = $this->getDocumentRoot();

        $componentsDir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/components';
        if (!mkdir($tmpVendorDir = $componentsDir.'/'.self::TEST_VENDOR_NAME)) {
            throw new Exception("Can't create tmp dir: `{$tmpVendorDir}`");
        }

        foreach (glob(__DIR__.'/resources/*', GLOB_ONLYDIR) as $componentDir) {
            symlink($componentDir, $componentsDir.'/'.basename($componentDir));
        }
    }

    protected function getDocumentRoot()
    {
        do {
            $documentRoot = strstr(__DIR__, 'bitrix', true);
            if ($documentRoot) {
                break;
            }

            $localRoot = strstr(__DIR__, 'local/vendor', true);
            if ($localRoot) {
                $documentRoot = $localRoot.'/../';
                break;
            }

            $dir = realpath(__DIR__.'/../../');
            do {
                if (is_dir($bitrixDir = $dir.'/bitrix')) {
                    $documentRoot = realpath($bitrixDir.'/../');
                    break;
                }
            } while ($dir = realpath($dir.'/../'));

        } while(false);

        return rtrim($documentRoot, '/');
    }

    /**
     * @dataProvider componentsDataProvider
     * @param $component
     * @param $template
     */
    public function testRender($component, $template)
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent($component, $template);
        $output = ob_get_clean();

        $this->assertSame('abc', $output);
    }

    public function componentsDataProvider()
    {
        $data = range('a', 'c');
        foreach ($data as &$value) {
            $value = array(self::TEST_VENDOR_NAME.':tools.twig', $value);
        }
        unset($value);

        return $data;
    }
}
