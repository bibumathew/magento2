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
 * Eav Form Fieldset Model
 *
 * @method \Magento\Eav\Model\Resource\Form\Fieldset _getResource()
 * @method \Magento\Eav\Model\Resource\Form\Fieldset getResource()
 * @method int getTypeId()
 * @method \Magento\Eav\Model\Form\Fieldset setTypeId(int $value)
 * @method string getCode()
 * @method \Magento\Eav\Model\Form\Fieldset setCode(string $value)
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Form\Fieldset setSortOrder(int $value)
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Form;

class Fieldset extends \Magento\Core\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eav_form_fieldset';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Form\Fieldset');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve resource collection instance wrapper
     *
     * @return \Magento\Eav\Model\Resource\Form\Fieldset\Collection
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    /**
     * Validate data before save data
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    protected function _beforeSave()
    {
        if (!$this->getTypeId()) {
            \Mage::throwException(__('Invalid form type.'));
        }
        if (!$this->getStoreId() && $this->getLabel()) {
            $this->setStoreLabel($this->getStoreId(), $this->getLabel());
        }

        return parent::_beforeSave();
    }

    /**
     * Retrieve fieldset labels for stores
     *
     * @return array
     */
    public function getLabels()
    {
        if (!$this->hasData('labels')) {
            $this->setData('labels', $this->_getResource()->getLabels($this));
        }
        return $this->_getData('labels');
    }

    /**
     * Set fieldset store labels
     * Input array where key - store_id and value = label
     *
     * @param array $labels
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    public function setLabels(array $labels)
    {
        return $this->setData('labels', $labels);
    }

    /**
     * Set fieldset store label
     *
     * @param int $storeId
     * @param string $label
     * @return \Magento\Eav\Model\Form\Fieldset
     */
    public function setStoreLabel($storeId, $label)
    {
        $labels = $this->getLabels();
        $labels[$storeId] = $label;

        return $this->setLabels($labels);
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            $this->setData('store_id', \Mage::app()->getStore()->getId());
        }
        return $this->_getData('store_id');
    }
}
