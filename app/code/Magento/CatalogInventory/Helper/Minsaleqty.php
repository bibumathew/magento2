<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * MinSaleQty value manipulation helper
 */
namespace Magento\CatalogInventory\Helper;

class Minsaleqty
{
    /**
     * Core data
     *
     * @var Magento_Core_Helper_Data
     */
    protected $_coreData = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData
    ) {
        $this->_coreData = $coreData;
    }

    /**
     * Retrieve fixed qty value
     *
     * @param mixed $qty
     * @return float|null
     */
    protected function _fixQty($qty)
    {
        return (!empty($qty) ? (float)$qty : null);
    }

    /**
     * Generate a storable representation of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        if (is_numeric($value)) {
            $data = (float)$value;
            return (string)$data;
        } else if (is_array($value)) {
            $data = array();
            foreach ($value as $groupId => $qty) {
                if (!array_key_exists($groupId, $data)) {
                    $data[$groupId] = $this->_fixQty($qty);
                }
            }
            if (count($data) == 1 && array_key_exists(\Magento\Customer\Model\Group::CUST_GROUP_ALL, $data)) {
                return (string)$data[\Magento\Customer\Model\Group::CUST_GROUP_ALL];
            }
            return serialize($data);
        } else {
            return '';
        }
    }

    /**
     * Create a value from a storable representation
     *
     * @param mixed $value
     * @return array
     */
    protected function _unserializeValue($value)
    {
        if (is_numeric($value)) {
            return array(
                \Magento\Customer\Model\Group::CUST_GROUP_ALL => $this->_fixQty($value)
            );
        } else if (is_string($value) && !empty($value)) {
            return unserialize($value);
        } else {
            return array();
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param mixed
     * @return bool
     */
    protected function _isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }
        unset($value['__empty']);
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('customer_group_id', $row) || !array_key_exists('min_sale_qty', $row)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array
     * @return array
     */
    protected function _encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $groupId => $qty) {
            $_id = $this->_coreData->uniqHash('_');
            $result[$_id] = array(
                'customer_group_id' => $groupId,
                'min_sale_qty' => $this->_fixQty($qty),
            );
        }
        return $result;
    }

    /**
     * Decode value from used in \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array
     * @return array
     */
    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $_id => $row) {
            if (!is_array($row) || !array_key_exists('customer_group_id', $row) || !array_key_exists('min_sale_qty', $row)) {
                continue;
            }
            $groupId = $row['customer_group_id'];
            $qty = $this->_fixQty($row['min_sale_qty']);
            $result[$groupId] = $qty;
        }
        return $result;
    }

    /**
     * Retrieve min_sale_qty value from config
     *
     * @param int $customerGroupId
     * @param mixed $store
     * @return float|null
     */
    public function getConfigValue($customerGroupId, $store = null)
    {
        $value = \Mage::getStoreConfig(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MIN_SALE_QTY, $store);
        $value = $this->_unserializeValue($value);
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $result = null;
        foreach ($value as $groupId => $qty) {
            if ($groupId == $customerGroupId) {
                $result = $qty;
                break;
            } else if ($groupId == \Magento\Customer\Model\Group::CUST_GROUP_ALL) {
                $result = $qty;
            }
        }
        return $this->_fixQty($result);
    }

    /**
     * Make value readable by \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param mixed $value
     * @return array
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->_unserializeValue($value);
        if (!$this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param mixed $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->_isEncodedArrayFieldValue($value)) {
            $value = $this->_decodeArrayFieldValue($value);
        }
        $value = $this->_serializeValue($value);
        return $value;
    }
}
