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
 * @package    Mage_ProductAlert
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert Price Customer collection
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author     Victor Tihonchuk <victor@varien.com>
 */
class Mage_ProductAlert_Model_Mysql4_Price_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    public function join($productId, $websiteId)
    {
        $this->getSelect()->join(
            array('alert' => $this->getTable('productalert/price')),
            'e.entity_id=alert.customer_id',
            array('alert_price_id', 'website_id', 'price', 'add_date', 'last_send_date', 'send_count', 'status')
        );

        $this->getSelect()->where('alert.product_id=?', $productId);
        if ($websiteId) {
            $this->getSelect()->where('alert.website_id=?', $websiteId);
        }
        $this->_setIdFieldName('alert_price_id');
        $this->addAttributeToSelect('*');

        return $this;
    }
}