<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Eav Form Element Model
 *
 * @method Magento_Eav_Model_Resource_Form_Element getResource()
 * @method int getTypeId()
 * @method Magento_Eav_Model_Form_Element setTypeId(int $value)
 * @method int getFieldsetId()
 * @method Magento_Eav_Model_Form_Element setFieldsetId(int $value)
 * @method int getAttributeId()
 * @method Magento_Eav_Model_Form_Element setAttributeId(int $value)
 * @method int getSortOrder()
 * @method Magento_Eav_Model_Form_Element setSortOrder(int $value)
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Eav_Model_Form_Element extends Magento_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_element';

    /**
     * @var Magento_Eav_Model_Config
     */
    protected $_eavConfig;

    /**
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Eav_Model_Config $eavConfig
     * @param Magento_Core_Model_Resource_Abstract $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_Eav_Model_Config $eavConfig,
        Magento_Core_Model_Resource_Abstract $resource = null,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento_Eav_Model_Resource_Form_Element');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Magento_Eav_Model_Resource_Form_Element
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return Magento_Eav_Model_Resource_Form_Element_Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws Magento_Core_Exception
     * @return Magento_Eav_Model_Form_Element
     */
    protected function _beforeSave()
    {
        if (!$this->getTypeId()) {
            throw new Magento_Core_Exception(__('Invalid form type.'));
        }
        if (!$this->getAttributeId()) {
            throw new Magento_Core_Exception(__('Invalid EAV attribute'));
        }

        return parent::_beforeSave();
    }

    /**
     * Retrieve EAV Attribute instance
     *
     * @return Magento_Eav_Model_Entity_Attribute
     */
    public function getAttribute()
    {
        if (!$this->hasData('attribute')) {
            $attribute = $this->_eavConfig->getAttribute($this->getEntityTypeId(), $this->getAttributeId());
            $this->setData('attribute', $attribute);
        }
        return $this->_getData('attribute');
    }
}
