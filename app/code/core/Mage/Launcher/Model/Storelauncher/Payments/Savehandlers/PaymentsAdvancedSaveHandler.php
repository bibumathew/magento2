<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPal Payments Advanced configuration save handler
 *
 * @category   Mage
 * @package    Mage_Launcher
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Launcher_Model_Storelauncher_Payments_Savehandlers_PaymentsAdvancedSaveHandler
    extends Mage_Launcher_Model_Storelauncher_Payments_PaymentSaveHandler
{
    /**
     * Save payment configuration data
     *
     * @param array $data
     * @return null
     * @throws Mage_Launcher_Exception
     */
    public function save(array $data)
    {
        $preparedData = $this->prepareData($data);
        $this->_backendConfigModel->setSection('paypal')
            ->setGroups($preparedData)
            ->save();
        $this->_config->reinit();
    }

    /**
     * Prepare payment configuration data for saving
     *
     * @param array $data
     * @return array prepared data
     * @throws Mage_Launcher_Exception
     */
    public function prepareData(array $data)
    {
        $preparedData = array();
        if (empty($data['groups']['payflow_advanced']['fields']['partner']['value'])) {
            throw new Mage_Launcher_Exception('Partner field is required.');
        }
        if (empty($data['groups']['payflow_advanced']['fields']['vendor']['value'])) {
            throw new Mage_Launcher_Exception('Vendor field is required.');
        }
        if (empty($data['groups']['payflow_advanced']['fields']['user']['value'])) {
            throw new Mage_Launcher_Exception('User field is required.');
        }
        if (empty($data['groups']['payflow_advanced']['fields']['pwd']['value'])) {
            throw new Mage_Launcher_Exception('Password field is required.');
        }

        $preparedData['payflow_advanced']['fields']['partner']['value'] =
            trim($data['groups']['payflow_advanced']['fields']['partner']['value']);
        $preparedData['payflow_advanced']['fields']['vendor']['value'] =
            trim($data['groups']['payflow_advanced']['fields']['vendor']['value']);
        $preparedData['payflow_advanced']['fields']['user']['value'] =
            trim($data['groups']['payflow_advanced']['fields']['user']['value']);
        $preparedData['payflow_advanced']['fields']['pwd']['value'] =
            trim($data['groups']['payflow_advanced']['fields']['pwd']['value']);

        // enable PayPal Payments Advanced
        $preparedData['global']['fields']['payflow_advanced']['value'] = 1;
        return $preparedData;
    }
}
