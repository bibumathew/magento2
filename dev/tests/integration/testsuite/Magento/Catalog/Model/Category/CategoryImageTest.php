<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * This test was moved to the separate file.
 * Because of fixture applying order magentoAppIsolation -> magentoDataFixture -> magentoConfigFixture
 * (https://wiki.magento.com/display/PAAS/Integration+Tests+Development+Guide
 * #IntegrationTestsDevelopmentGuide-ApplyingAnnotations)
 * config fixtures can't be applied before data fixture.
 */
class Magento_Catalog_Model_Category_CategoryImageTest extends PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $_oldLogActive;

    /** @var string */
    protected $_oldExceptionFile;

    /** @var string */
    protected $_oldWriterModel;

    protected function setUp()
    {
        /** @var $configModel Magento_Core_Model_Config */
        $configModel = Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_Core_Model_Config');
        $this->_oldLogActive = Mage::app()->getStore()->getConfig('dev/log/active');
        $this->_oldExceptionFile = Mage::app()->getStore()->getConfig('dev/log/exception_file');
        $this->_oldWriterModel = (string)$configModel->getNode('global/log/core/writer_model');
    }

    protected function tearDown()
    {
        Mage::app()->getStore()->setConfig('dev/log/active', $this->_oldLogActive);
        $this->_oldLogActive = null;

        Mage::app()->getStore()->setConfig('dev/log/exception_file', $this->_oldExceptionFile);
        $this->_oldExceptionFile = null;

        /** @var $configModel Magento_Core_Model_Config */
        $configModel = Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_Core_Model_Config');
        $configModel->setNode('global/log/core/writer_model', $this->_oldWriterModel);
        $this->_oldWriterModel = null;

        /**
         * @TODO: refactor this test
         * Changing store configuration in such a way totally breaks the idea of application isolation.
         * Class declaration in data fixture file is dumb too.
         * Added a quick fix to be able run separate tests with "phpunit --filter testMethod"
         */
        if (class_exists('Magento_Catalog_Model_Category_CategoryImageTest_StubZendLogWriterStreamTest', false)) {
            Magento_Catalog_Model_Category_CategoryImageTest_StubZendLogWriterStreamTest::$exceptions = array();
        }
    }

    /**
     * Test that there is no exception '$_FILES array is empty' in \Magento\File\Uploader::_setUploadFileId()
     * if category image was not set
     *
     * @magentoDataFixture Magento/Catalog/Model/Category/_files/category_without_image.php
     */
    public function testSaveCategoryWithoutImage()
    {
        /** @var $objectManager Magento_TestFramework_ObjectManager */
        $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();

        /** @var $category Magento_Catalog_Model_Category */
        $category = $objectManager->get('Magento\Core\Model\Registry')
            ->registry('_fixture/Magento\Catalog\Model\Category');
        $this->assertNotEmpty($category->getId());

        foreach (Magento_Catalog_Model_Category_CategoryImageTest_StubZendLogWriterStreamTest::$exceptions
                 as $exception) {
            $this->assertNotContains('$_FILES array is empty', $exception['message']);
        }
    }
}
