<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Sys config source model for stub page statuses
 *
 */
class Enterprise_WebsiteRestriction_Model_System_Config_Source_Http
extends Varien_Object
{
    /**
     * Get options for select
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Enterprise_WebsiteRestriction_Model_Mode::HTTP_503,
                'label' => Mage::helper('enterprise_websiterestriction')->__('503 Service unavailable'),
            ),
            array(
                'value' => Enterprise_WebsiteRestriction_Model_Mode::HTTP_200,
                'label' => Mage::helper('enterprise_websiterestriction')->__('200 OK'),
            ),
        );
    }
}
