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
 * @package    Mage_Protx
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Protx Modes Resource
 *
 * @category   Mage
 * @package    Mage_Protx
 * @name       Mage_Protx_Model_Source_ModeAction
 * @author     Dmitriy Volik <dmitriy.volik@varien.com>
 */

class Mage_Protx_Model_Source_ModeAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Protx_Model_Config::MODE_SIMULATOR, 'label' => Mage::helper('protx')->__('Simulator')),
            array('value' => Mage_Protx_Model_Config::MODE_TEST, 'label' => Mage::helper('protx')->__('Test')),
            array('value' => Mage_Protx_Model_Config::MODE_LIVE, 'label' => Mage::helper('protx')->__('Live')),
        );
    }
}



