<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Di
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
require_once __DIR__ . '/Generator/TestAsset/SourceClassWithNamespace.php';
require_once __DIR__ . '/Generator/TestAsset/ParentClassWithNamespace.php';
/**
 * @magentoAppIsolation enabled
 */
class Magento_Di_GeneratorTest extends PHPUnit_Framework_TestCase
{
    const CLASS_NAME_WITHOUT_NAMESPACE = 'Magento_Di_Generator_TestAsset_SourceClassWithoutNamespace';
    const CLASS_NAME_WITH_NAMESPACE = 'Magento\Di\Generator\TestAsset\SourceClassWithNamespace';

    /**
     * @var string
     */
    protected $_includePath;

    /**
     * @var Magento_Di_Generator
     */
    protected $_generator;

    protected function setUp()
    {
        $this->_includePath = get_include_path();

        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = Mage::getObjectManager()->get('Mage_Core_Model_Dir');
        $generationDirectory = $dirs->getDir(Mage_Core_Model_Dir::VAR_DIR) . '/generation';

        Magento_Autoload_IncludePath::addIncludePath($generationDirectory);

        $ioObject = new Magento_Di_Generator_Io(
            new Varien_Io_File(),
            new Magento_Autoload_IncludePath(),
            $generationDirectory
        );
        $this->_generator = Mage::getObjectManager()->create('Magento_Di_Generator', array('ioObject' => $ioObject));
    }

    protected function tearDown()
    {
        /** @var $dirs Mage_Core_Model_Dir */
        $dirs = Mage::getObjectManager()->get('Mage_Core_Model_Dir');
        $generationDirectory = $dirs->getDir(Mage_Core_Model_Dir::VAR_DIR) . '/generation';
        Varien_Io_File::rmdirRecursive($generationDirectory);

        set_include_path($this->_includePath);
        unset($this->_generator);
    }

    public function testGenerateClassFactoryWithoutNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITHOUT_NAMESPACE . 'Factory';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        /** @var $factory Magento_ObjectManager_Factory */
        $factory = Mage::getObjectManager()->create($factoryClassName);
        $object = $factory->create();
        $this->assertInstanceOf(self::CLASS_NAME_WITHOUT_NAMESPACE, $object);
    }

    public function testGenerateClassFactoryWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Factory';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        /** @var $factory Magento_ObjectManager_Factory */
        $factory = Mage::getObjectManager()->create($factoryClassName);

        $object = $factory->create();
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $object);
    }

    public function testGenerateClassProxyWithoutNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITHOUT_NAMESPACE . 'Proxy';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        $proxy = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITHOUT_NAMESPACE, $proxy);

        $this->_verifyProxyMethods(self::CLASS_NAME_WITHOUT_NAMESPACE, $proxy);
    }

    public function testGenerateClassProxyWithNamespace()
    {
        $factoryClassName = self::CLASS_NAME_WITH_NAMESPACE . 'Proxy';
        $this->assertTrue($this->_generator->generateClass($factoryClassName));

        $proxy = Mage::getObjectManager()->create($factoryClassName);
        $this->assertInstanceOf(self::CLASS_NAME_WITH_NAMESPACE, $proxy);

        $this->_verifyProxyMethods(self::CLASS_NAME_WITH_NAMESPACE, $proxy);
    }

    /**
     * @param string $class
     * @param object $proxy
     */
    protected function _verifyProxyMethods($class, $proxy)
    {
        $expectedMethods = array();
        $reflectionObject = new ReflectionClass(new $class());
        $publicMethods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic())) {
                $expectedMethods[$method->getName()] = $method->getParameters();
            }
        }

        $actualMethods = array();
        $reflectionObject = new ReflectionClass($proxy);
        $publicMethods = $reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic())) {
                $actualMethods[$method->getName()] = $method->getParameters();
            }
        }

        $this->assertEquals($expectedMethods, $actualMethods);
    }
}
