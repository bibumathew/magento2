<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Config config system template source
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Backend_Model_Config_Source_Email_Template extends Magento_Object
    implements Magento_Core_Model_Option_ArrayInterface
{
    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$collection = Mage::registry('config_system_email_template')) {
            $collection = Mage::getResourceModel('Magento_Core_Model_Resource_Email_Template_Collection')
                ->load();

            Mage::register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        $templateName = Mage::helper('Magento_Backend_Helper_Data')->__('Default Template');
        $nodeName = str_replace('/', '_', $this->getPath());
        $templateLabelNode = Mage::app()->getConfig()->getNode(
            Magento_Core_Model_Email_Template::XML_PATH_TEMPLATE_EMAIL . '/' . $nodeName . '/label'
        );
        if ($templateLabelNode) {
            $module = (string)$templateLabelNode->getParent()->getAttribute('module');
            $templateName = Mage::helper($module)->__((string)$templateLabelNode);
            $templateName = Mage::helper('Magento_Backend_Helper_Data')->__('%s (Default)', $templateName);
        }
        array_unshift(
            $options,
            array(
                'value'=> $nodeName,
                'label' => $templateName
            )
        );
        return $options;
    }

}
