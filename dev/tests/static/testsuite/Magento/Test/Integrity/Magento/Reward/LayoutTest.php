<?php
/**
 * Validator of class names in Reward nodes
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Test\Integrity\Magento\Reward;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider layoutFileDataProvider
     */
    public function testInitRewardTypeClasses($file)
    {
        $xml = simplexml_load_file($file);
        $nodes = $xml->xpath('//argument[@name="reward_type"]') ? : array();
        $errors = array();
        /** @var \SimpleXMLElement $node */
        foreach ($nodes as $node) {
            $class = (string)$node;
            if (!\Magento\TestFramework\Utility\Files::init()->classFileExists($class, $path)) {
                $errors[] = "'{$class}' => '{$path}'";
            }
        }
        if ($errors) {
            $this->fail("Invalid class declarations in {$file}. Files are not found in code pools:\n"
                . implode(PHP_EOL, $errors) . PHP_EOL
            );
        }
    }

    /**
     * @return array
     */
    public function layoutFileDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getLayoutFiles();
    }
}
