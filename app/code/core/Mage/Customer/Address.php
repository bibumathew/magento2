<?php
/**
 * Customer address
 *
 * @package    Ecom
 * @subpackage Customer
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
abstract class Mage_Customer_Address extends Varien_Data_Object 
{
    protected $_types = array();
    
    /**
     * Constructor receives $address as array of fields for new address or integer to load existing id
     *
     * @param array|integer $address
     */
    public function __construct($address=false) 
    {
        parent::__construct();
        
        if (is_numeric($address)) {
            $this->loadByAddressId($address);
        } elseif (is_array($address)) {
            $this->setData($address);
        }
    }
    
    public function load($addressId)
    {
        $this->loadByAddressId($addressId);
    }
    
    public function save()
    {
        $addressModel = Mage::getResourceModel('customer', 'address');
        
        if ($this->getAddressId()) {
            $this->update($this);
        } else {
            $this->insert($this);
        }
    }
    
    public function getStreet($line=0)
    {
        if (-1===$line) {
            return $this->getData('street');
        } else {
            $arr = explode("\n", trim($this->getData('street')));
            if (0===$line) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }
    
    public function setStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('street', $street);
    }
    
    /**
     * Create fields street1, street2, etc.
     * 
     * To be used in controllers for views data
     *
     */
    public function explodeStreetAddress()
    {
        $streetLines = $this->getStreet();
        foreach ($streetLines as $i=>$line) {
            $this->setData('street'.($i+1), $line);
        }
    }
    
    /**
     * To be used when processing _POST
     *
     */
    public function implodeStreetAddress()
    {
        $this->setStreet($this->getData('street'));
    }
    
    public function getType($type='', $is_primary=null)
    {
        if (''===$type) {
            $types = $this->_types;
            if (!is_null($is_primary)) {
                foreach ($types as $code=>$t) {
                    if ($t['is_primary']!==(boolean)$is_primary) {
                        unset($types[$code]);
                    }
                }
            }
            return $types;
            
        } elseif (isset($this->_types[$type])) {
            $t = $this->_types[$type];
            if (!is_null($is_primary)) {
                if ($t['is_primary']===(boolean)$is_primary) {
                    return $t;
                } else {
                    return false;
                }
            }
        }
        return false;
    }
    
    public function setType($type, $isPrimary=null)
    {
        if (is_array($type)) {
            foreach ($type as $k=>$v) {
                $this->setType($k, $v['is_primary']);
            }
        } else {
            if (!is_null($isPrimary)) {
                $this->_types[$type] = array('is_primary'=>$isPrimary);
            } else {
                unset($this->_types[$type]);
            }
        }
    }
    
    public function toString($format='')
    {
        if (empty($format)) {
            $str = implode(', ', $this->getData());
        } else {
            $str = '// TODO: address string format';
        }
        return $str;
    }
    
    public function validateCreate()
    {
        return true;
    }
    
    public function validateUpdate()
    {
        return true;
    }
}