<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_AttributeSet
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Attribute Set creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_AttributeSet_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog -> Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_attribute_sets');
    }

    /**
     * <p>Attribute Set creation - based on Default</p>
     *
     * @return string
     * @test
     * @TestlinkId TL-MAGE-3161
     */
    public function basedOnDefault()
    {
        //Data
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set');
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        return $setData['set_name'];
    }

    /**
     * <p>Attribute Set creation - existing name</p>
     *
     * @param string $attributeSetName
     *
     * @test
     * @depends basedOnDefault
     * @TestlinkId TL-MAGE-3164
     */
    public function withNameThatAlreadyExists($attributeSetName)
    {
        //Data
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set', array('set_name' => $attributeSetName));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->addParameter('attributeSetName', $setData['set_name']);
        $this->assertMessagePresent('error', 'error_attribute_set_exist');
    }

    /**
     * <p>Attribute Set creation - empty name</p>
     *
     * @test
     * @depends basedOnDefault
     * @TestlinkId TL-MAGE-3162
     */
    public function withEmptyName()
    {
        //Data
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set', array('set_name' => ''));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->addFieldIdToMessage('field', 'set_name');
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating Attribute Set with long values in required fields</p>
     *
     * @test
     * @depends basedOnDefault
     * @TestlinkId TL-MAGE-3163
     */
    public function withLongValues()
    {
        //Data
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set',
            array('set_name' => $this->generate('string', 255, ':alnum:')));
        $attributeSetSearch['set_name'] = $setData['set_name'];
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        //Steps
        $this->attributeSetHelper()->openAttributeSet($attributeSetSearch);
        $this->assertTrue($this->verifyForm($attributeSetSearch), $this->getParsedMessages());
    }

    /**
     * <p>Creating Attribute Set using special characters for set name</p>
     *
     * @test
     * @depends basedOnDefault
     * @TestlinkId TL-MAGE-3165
     */
    public function withSpecialCharacters()
    {
        //Data
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set',
            array('set_name' => $this->generate('string', 32, ':punct:')));
        $setData['set_name'] = preg_replace('/<|>/', '', $setData['set_name']);
        $attributeSetSearch['set_name'] = $setData['set_name'];
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        //Steps
        $this->attributeSetHelper()->openAttributeSet($attributeSetSearch);
        $this->assertTrue($this->verifyForm($attributeSetSearch), $this->getParsedMessages());
    }

    /**
     * <p>Add user product attributes</p>
     *
     * @return array
     * @test
     * @depends basedOnDefault
     */
    public function addUserProductAttributesToNewGroup()
    {
        //Data
        $groupName = $this->generate('string', 5, ':lower:') . '_test_group';
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attributes');
        $setData = $this->loadDataSet('AttributeSet', 'attribute_set');
        $attrCodes = array();
        foreach ($attrData as $value) {
            if (is_array($value) && array_key_exists('attribute_code', $value)) {
                $attrCodes[] = $value['attribute_code'];
            }
        }
        $setData['associated_attributes'][$groupName] = $attrCodes;
        //Steps
        $this->navigate('manage_attributes');
        foreach ($attrData as $value) {
            $this->productAttributeHelper()->createAttribute($value);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_attribute');
        }
        //Steps
        $this->assertPreConditions();
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');

        return $setData;
    }

    /**
     * <p>Attribute Set creation - based on Custom</p>
     *
     * @param array $setData
     *
     * @test
     * @depends addUserProductAttributesToNewGroup
     * @TestlinkId TL-MAGE-3160
     */
    public function basedOnCustom($setData)
    {
        //Data
        $setDataCustom = $this->loadDataSet('AttributeSet', 'attribute_set', array('based_on' => $setData['set_name']));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setDataCustom);
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
    }
}