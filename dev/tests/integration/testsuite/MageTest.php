<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class MageTest extends PHPUnit_Framework_TestCase
{
    public function testIsInstalled()
    {
        $this->assertTrue(Mage::isInstalled());
    }

    /**
     * @param int|null $level
     * @param string $file
     * @param bool $forceLog
     * @param int $expectedLevel
     * @param string $expectedKey
     * @param bool $expectsAddLog
     * @dataProvider logDataProvider
     * @throws Exception
     */
    public function testLog($level, $file, $forceLog, $expectedLevel, $expectedKey, $expectsAddLog)
    {
        $message = uniqid();
        $objectManager = Magento_Test_Helper_Bootstrap::getObjectManager();
        /** @var $logger Magento_Core_Model_Logger|PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMock('Magento_Core_Model_Logger', array('log', 'addStreamLog'), array(), '', false);
        $realLogger = $objectManager->get('Magento_Core_Model_Logger');
        $objectManager->addSharedInstance($logger, 'Magento_Core_Model_Logger');
        try {
            $logger->expects($this->once())->method('log')->with($message, $expectedLevel, $expectedKey);
            if ($expectsAddLog) {
                $logger->expects($this->once())->method('addStreamLog');
            }
            Mage::log($message, $level, $file, $forceLog);
            $objectManager->addSharedInstance($realLogger, 'Magento_Core_Model_Logger');
        } catch (Exception $e) {
            $objectManager->addSharedInstance($realLogger, 'Magento_Core_Model_Logger');
            throw $e;
        }

    }

    /**
     * @return array
     */
    public function logDataProvider()
    {
        return array(
            array(null, '', false, Zend_Log::DEBUG, Magento_Core_Model_Logger::LOGGER_SYSTEM, false),
            array(Zend_Log::CRIT, 'system.log', true, Zend_Log::CRIT, Magento_Core_Model_Logger::LOGGER_SYSTEM, false),
            array(null, 'exception.log', false, Zend_Log::DEBUG, Magento_Core_Model_Logger::LOGGER_EXCEPTION, false),
            array(null, 'custom.log', false, Zend_Log::DEBUG, 'custom.log', true, false),
            array(null, 'exception.log', true, Zend_Log::DEBUG, Magento_Core_Model_Logger::LOGGER_EXCEPTION, true),
        );
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture current_store dev/log/file php://output
     * @link http://us3.php.net/manual/en/wrappers.php
     */
    public function testLogWrapper()
    {
        // @magentoConfigFixture is applied after initialization, so we need to do this again
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize();
        $this->expectOutputRegex('/test/');
        Mage::app()->getStore(true);
        Mage::log('test');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLogWrapperDirectly()
    {
        $this->expectOutputRegex('/test/');
        Mage::log('test', null, 'php://output');
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture global/log/core/writer_model Zend_Log_Writer_Mail
     * @magentoAppIsolation enabled
     */
    public function testLogUnsupportedWrapper()
    {
        // initialize again, because config fixture is applied after initialization
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize();
        $logEntry = microtime();
        Mage::log($logEntry);
        $logFile = Mage::getBaseDir('log') . '/system.log';
        $this->assertFileExists($logFile);
        $this->assertContains($logEntry, file_get_contents($logFile));
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture current_store dev/log/exception_file php://output
     * @magentoAppIsolation enabled
     */
    public function testLogException()
    {
        // reinitialization is needed here, too
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize();
        Mage::app()->getStore(true);
        $msg = uniqid();
        $exception = new Exception((string)$msg);
        Mage::logException($exception);
        $this->expectOutputRegex('/' . $msg . '/');
    }

    /**
     * @param string $classId
     * @param string $expectedClassName
     * @dataProvider getModelDataProvider
     */
    public function testGetModel($classId, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getModel($classId));
    }

    /**
     * @return array
     */
    public function getModelDataProvider()
    {
        return array(
            array('Magento_Core_Model_Config', 'Magento_Core_Model_Config')
        );
    }

    /**
     * @param string $classId
     * @param string $expectedClassName
     * @dataProvider getResourceModelDataProvider
     */
    public function testGetResourceModel($classId, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getResourceModel($classId));
    }

    /**
     * @return array
     */
    public function getResourceModelDataProvider()
    {
        return array(
            array('Magento_Core_Model_Resource_Config', 'Magento_Core_Model_Resource_Config')
        );
    }

    /**
     * @param string $module
     * @param string $expectedClassName
     * @dataProvider getResourceHelperDataProvider
     */
    public function testGetResourceHelper($module, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getResourceHelper($module));
    }

    /**
     * @return array
     */
    public function getResourceHelperDataProvider()
    {
        return array(
            array('Magento_Core', 'Magento_Core_Model_Resource_Helper_Abstract')
        );
    }
}
