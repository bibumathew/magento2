<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community1701_Mage_SystemConfiguration_Helper extends Core_Mage_SystemConfiguration_Helper
{
    /**
     * PayPal System Configuration
     *
     * @param array|string $parameters
     *
     * @throws RuntimeException
     */
    public function configurePaypal($parameters)
    {
        if (is_string($parameters)) {
            $elements = explode('/', $parameters);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $parameters = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $chooseScope = (isset($parameters['configuration_scope'])) ? $parameters['configuration_scope'] : null;
        $country = (isset($parameters['merchant_country'])) ? $parameters['merchant_country'] : null;
        $configuration = (isset($parameters['configuration'])) ? $parameters['configuration'] : array();
        if ($chooseScope) {
            $this->changeConfigurationScope('current_configuration_scope', $chooseScope);
        }
        $this->openConfigurationTab('sales_payment_methods');
        $this->disableAllPaypalMethods();
        if ($country) {
            $xpath = $this->_getControlXpath('dropdown', 'merchant_country');
            $toSelect = $xpath . '//option[normalize-space(text())="' . $country . '"]';
            $isSelected = $toSelect . '[@selected]';
            if (!$this->isElementPresent($isSelected)) {
                $this->saveForm('save_config');
                $this->addParameter('country', $this->getValue($toSelect));
                $this->fillDropdown('merchant_country', $country);
                $this->waitForPageToLoad($this->_browserTimeoutPeriod);
                $this->validatePage();
            }
        }
        foreach ($configuration as $payment) {
            $paymentName = (isset($payment['payment_name'])) ? $payment['payment_name'] : null;
            $generalSection = (isset($payment['general_fieldset'])) ? $payment['general_fieldset'] : null;
            if (is_null($paymentName) || is_null($generalSection)) {
                throw new RuntimeException('Error');
            }
            $this->disclosePaypalFieldset($generalSection);
            if ($this->controlIsVisible('button', $paymentName . '_configure')) {
                $this->clickButton($paymentName . '_configure', false);
            }
            foreach ($payment as $dataSet) {
                if (!is_array($dataSet)) {
                    continue;
                }
                $fieldsetName = $this->disclosePaypalFieldset($dataSet['path']);
                $forFill = array();
                foreach ($dataSet['data'] as $key => $value) {
                    $forFill[$paymentName . '_' . $key] = $value;
                }
                $this->fillFieldset($forFill, $fieldsetName);
            }
        }
        $this->saveForm('save_config');
        $this->assertMessagePresent('success', 'success_saved_config');
        foreach ($configuration as $data) {
            foreach ($data as $dataSet) {
                if (!is_array($dataSet)) {
                    continue;
                }
                $this->verifyForm($dataSet['data'], 'sales_payment_methods');
            }
        }
        if ($this->getParsedMessages('verification')) {
            foreach ($this->getParsedMessages('verification') as $key => $errorMessage) {
                if (preg_match('#(\'all\' \!\=)|(\!\= \'\*\*)|(\'all\')#i', $errorMessage)) {
                    unset(self::$_messages['verification'][$key]);
                }
            }
            $this->assertEmptyVerificationErrors();
        }
    }

    /**
     * Disclose Paypal fieldset
     *
     * @param string $path
     *
     * @return string Fieldset name for filling in
     */
    public function disclosePaypalFieldset($path)
    {
        $fullPath = explode('/', $path);
        $fullPath = array_map('trim', $fullPath);
        foreach ($fullPath as $node) {
            $class = $this->getAttribute($this->_getControlXpath('fieldset', $node) . '@class');
            if (!preg_match('/active/', $class)) {
                $this->clickControl('link', $node . '_section', false);
            }
        }

        return end($fullPath);
    }

    /**
     * Select country for paypal
     *
     * @param string $country
     */
    public function selectPaypalCountry($country)
    {
        $xpath = $this->_getControlXpath('dropdown', 'merchant_country');
        $toSelect = $xpath . '//option[normalize-space(text())="' . $country . '"]';
        $isSelected = $toSelect . '[@selected]';
        if (!$this->isElementPresent($isSelected)) {
            $this->addParameter('country', $this->getValue($toSelect));
            $this->fillDropdown('merchant_country', $country);
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        }
    }

    /**
     * Disable all active paypal payment methods
     *
     * @return null
     */
    public function disableAllPaypalMethods()
    {
        $xpath = $this->_getControlXpath('button', 'active_paypal_method');
        if (!$this->isElementPresent($xpath)) {
            return;
        }
        $closePaypalFieldsetButtons = array();
        $openedFieldsets = array();
        foreach ($this->getCurrentUimapPage()->getAllButtons() as $key => $value) {
            if (preg_match('/_close$/', $key)) {
                $closePaypalFieldsetButtons[preg_replace('/_close$/', '', $key)] = $value;
            }
        }
        while ($this->isElementPresent($xpath)) {
            $idRegExp = preg_quote('@id=\'' . $this->getAttribute($xpath . '@id'));
            foreach ($closePaypalFieldsetButtons as $name => $xpathButton) {
                if (preg_match('/' . $idRegExp . '/', $xpathButton)) {
                    if (in_array($name, $openedFieldsets)) {
                        break 2;
                    }
                    $this->click($xpath);
                    $openedFieldsets[] = $name;
                    $dropdownXpath = $this->_getControlXpath('dropdown', $name . '_enable');
                    if ($this->isEditable($dropdownXpath)) {
                        $this->fillDropdown($name . '_enable', 'No', $dropdownXpath);
                    }
                    break;
                }
            }
        }
    }
}