<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Weee
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml weee tax item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Weee_Block_Renderer_Weee_Tax extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;
    protected $_countries = null;
    protected $_websites = null;

    public function __construct()
    {
        $this->setTemplate('renderer/tax.phtml');
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $this->_setAddButton();
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getValues()
    {
        $values =array();
        $data = $this->getElement()->getValue();

        if (is_array($data) && count($data)) {
            usort($data, array($this, '_sortWeeeTaxes'));
            $values = $data;
        }
        return $values;
    }

    protected function _sortWeeeTaxes($a, $b)
    {
        if ($a['website_id']!=$b['website_id']) {
            return $a['website_id']<$b['website_id'] ? -1 : 1;
        }
        if ($a['country']!=$b['country']) {
            return $a['country']<$b['country'] ? -1 : 1;
        }
        return 0;
    }

    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    public function isMultiWebsites()
    {
        return !Mage::app()->hasSingleStore();
    }

    public function getCountries()
    {
        if (is_null($this->_countries)) {
            $this->_countries = Mage::getModel('Mage_Directory_Model_Config_Source_Country')
                ->toOptionArray();
        }

        return $this->_countries;
    }

    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }
        $websites = array();
        $websites[0] = array(
            'name'      => $this->__('All Websites'),
            'currency'  => Mage::app()->getBaseCurrencyCode()
        );

        if (!Mage::app()->hasSingleStore() && !$this->getElement()->getEntityAttribute()->isScopeGlobal()) {
            if ($storeId = $this->getProduct()->getStoreId()) {
                $website = Mage::app()->getStore($storeId)->getWebsite();
                $websites[$website->getId()] = array(
                    'name'      => $website->getName(),
                    'currency'  => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                );
            } else {
                foreach (Mage::app()->getWebsites() as $website) {
                    if (!in_array($website->getId(), $this->getProduct()->getWebsiteIds())) {
                        continue;
                    }
                    $websites[$website->getId()] = array(
                        'name'      => $website->getName(),
                        'currency'  => $website->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                    );
                }
            }
        }
        $this->_websites = $websites;
        return $this->_websites;
    }

    protected function _setAddButton()
    {
        $this->addChild('add_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Add Tax'),
            'onclick'   => "weeeTaxControl.addItem('".$this->getElement()->getHtmlId()."')",
            'class' => 'add'
        ));
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

}
