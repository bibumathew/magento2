<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Adminhtml\Model\LayoutUpdate;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    public function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param string $layoutUpdate
     * @param boolean $isSchemaValid
     * @return \Magento\Adminhtml\Model\LayoutUpdate\Validator
     */
    protected function _createValidator($layoutUpdate, $isSchemaValid = true)
    {
        $modulesReader = $this->getMockBuilder('Magento\Module\Dir\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $modulesReader->expects($this->exactly(2))
            ->method('getModuleDir')
            ->with('etc', 'Magento_Core')
            ->will($this->returnValue('dummyDir'));

        $domConfigFactory = $this->getMockBuilder('Magento\Config\DomFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $params = array(
            'xml' => '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . trim($layoutUpdate)
            . '</layout>',
            'schemaFile' => 'dummyDir/layout_single.xsd'
        );

        $exceptionMessage = 'validation exception';
        $domConfigFactory->expects($this->once())
            ->method('createDom')
            ->with($this->equalTo($params))
            ->will(
                $isSchemaValid
                    ? $this->returnSelf()
                    : $this->throwException(new \Magento\Config\Dom\ValidationException($exceptionMessage))
            );

        $model = $this->_objectHelper->getObject('Magento\Adminhtml\Model\LayoutUpdate\Validator', array(
            'modulesReader' => $modulesReader,
            'domConfigFactory' => $domConfigFactory,
        ));

        return $model;
    }

    /**
     * @dataProvider testIsValidNotSecurityCheckDataProvider
     * @param string $layoutUpdate
     * @param boolean $isValid
     * @param boolean $expectedResult
     * @param array $messages
     */
    public function testIsValidNotSecurityCheck($layoutUpdate, $isValid, $expectedResult, $messages)
    {
        $model = $this->_createValidator($layoutUpdate, $isValid);
        $this->assertEquals(
            $model->isValid(
                $layoutUpdate,
                \Magento\Adminhtml\Model\LayoutUpdate\Validator::LAYOUT_SCHEMA_SINGLE_HANDLE,
                false
            ),
            $expectedResult
        );
        $this->assertEquals($model->getMessages(), $messages);
    }

    /**
     * @return array
     */
    public function testIsValidNotSecurityCheckDataProvider()
    {
        return array(
            array('test', true, true, array()),
            array('test', false, false, array(
                \Magento\Adminhtml\Model\LayoutUpdate\Validator::XML_INVALID =>
                'Please correct the XML data and try again. validation exception'
            )),
        );
    }

    /**
     * @dataProvider testIsValidSecurityCheckDataProvider
     * @param string $layoutUpdate
     * @param boolean $expectedResult
     * @param array $messages
     */
    public function testIsValidSecurityCheck($layoutUpdate, $expectedResult, $messages)
    {
        $model = $this->_createValidator($layoutUpdate);
        $this->assertEquals(
            $model->isValid(
                $layoutUpdate,
                \Magento\Adminhtml\Model\LayoutUpdate\Validator::LAYOUT_SCHEMA_SINGLE_HANDLE,
                true
            ),
            $expectedResult
        );
        $this->assertEquals($model->getMessages(), $messages);
    }

    /**
     * @return array
     */
    public function testIsValidSecurityCheckDataProvider()
    {
        $insecureHelper = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="helper" helper="Helper_Class"/>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        $insecureUpdater = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="string">
                  <updater>Updater_Model</updater>
                  <value>test</value>
              </argument>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        $secureLayout = <<<XML
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="handleId">
        <block class="Block_Class">
          <arguments>
              <argument name="test" xsi:type="string">test</argument>
          </arguments>
        </block>
    </handle>
</layout>
XML;
        return array(
            array($insecureHelper, false, array(
                \Magento\Adminhtml\Model\LayoutUpdate\Validator::HELPER_ARGUMENT_TYPE =>
                'Helper arguments should not be used in custom layout updates.'
            )),
            array($insecureUpdater, false, array(
                \Magento\Adminhtml\Model\LayoutUpdate\Validator::UPDATER_MODEL =>
                'Updater model should not be used in custom layout updates.'
            )),
            array($secureLayout, true, array()),
        );
    }
}
