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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Reports_Model_Report extends Mage_Core_Model_Abstract
{
    protected $_reportModel;
    protected $_periodStart;
    protected $_periodEnd;

    public function initCollection($collectionClass)
    {
        $this->_reportModel = Mage::getResourceModel($collectionClass)
            ->setPageSize($this->getPageSize());

        $this->_reportModel->load();
        return $this;
    }

    public function getCollection()
    {
        return $this->_reportModel;
    }
}