<?php
/**
 * Magento
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Various
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 *
 */

class Community2_Mage_Various_SaveConfigurationTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Bug Cover<p/>
     * <p>Verification of MAGETWO-1918:</p>
     * <p>"Use Default" is checked again after saving multiselect attribute config if option does not contain value</p>
     *
     * <p>Steps:</p>
     * <p>1. Log in to Backend</p>
     * <p>2. Go to System -> Configuration > General > Locale Options</p>
     * <p>3. Select store view scope</p>
     * <p>4. Uncheck "Use Default" checkbox for "Weekend Days"</p>
     * <p>5. Deselect all Weekend Days</p>
     * <p>6. Save Config</p>
     * <p>Expected results:</p>
     * <p> Checkbox "Use Default" for "Weekend Days" should be unchecked. No one Weekend Day should be selected</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6290
     */
    public function saveMultiselectWithNoSelectedValuesOnStoreView ()
    {
        $this->markTestIncomplete('MAGETWO-1918');
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->selectStoreScope('dropdown', 'current_configuration_scope', 'Main Website');
        $this->systemConfigurationHelper()->expandFieldSet('locale_options');
        if (!$this->getControlElement('checkbox','weekend_days_use_default')->selected()) {
            $this->fillMultiselect('weekend_days', 'Sunday');
            $this->clickControl('checkbox', 'weekend_days_use_default', false);
            $this->clickButton('save_config');
            $this->assertMessagePresent('success', 'success_saved_config');
        } else {
            $this->clickControl('checkbox', 'weekend_days_use_default', false);
        }
        $this->fillMultiselect('weekend_days', '');
        $this->clickButton('save_config');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_config');
        $this->assertFalse($this->getControlElement('checkbox', 'weekend_days_use_default')->selected(),
            'Use Default checkbox checked');
        $selectedOptions = $this->select($this->getControlElement('multiselect', 'weekend_days'))->selectedLabels();
        $this->assertCount(0, $selectedOptions, 'Some day still selected in Weekend Days multiselect');
    }
}
