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
 * @category   Mage
 * @package    Mage_Cms
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer alert type abstract model
 *
 * @category   Mage
 * @package    Mage_CustomerAlert
 * @author     Vasily Selivanov <vasily@varien.com>
 */

abstract class Mage_CustomerAlert_Model_Type_Abstract extends Mage_CustomerAlert_Model_Type
{
    
    public function getAlertText()
    {   
        if($this->getAlertHappened()){
            $changedValues = $this->getAlertChangedValues();
            $this->_oldValue = $changedValues['old_value'];
            $this->_newValue = $changedValues['new_value'];
            $this->_date = $changedValues['date'];
            return $this->getAlertHappenedText(); 
        } else {
            return $this->getAlertNotHappenedText();            
        }
        
    }
    
    abstract public function getAlertHappenedText();
    abstract public function getAlertNotHappenedText();
    abstract public function checkBefore(Mage_Catalog_Model_Product $oldProduct, Mage_Catalog_Model_Product $newProduct);
    abstract public function checkAfter(Mage_Catalog_Model_Product $oldProduct, Mage_Catalog_Model_Product $newProduct);
}
