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
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_Staging_Model_Staging_State_Website_Rollback extends Enterprise_Staging_Model_Staging_State_Website_Abstract
{
    /**
     * Run rollback process
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @return Enterprise_Staging_Model_Staging_State_Website_Rollback
     */
    protected function _run(Enterprise_Staging_Model_Staging $staging)
    {
        $mapper         = $staging->getMapperInstance();
        $stagingItems   = $mapper->getStagingItems();
        foreach ($stagingItems as $stagingItem) {
            $adapter = $this->getItemAdapterInstanse($stagingItem);
            $adapter->rollback($staging);
        }
        return $this;
    }

    /**
     * Set complete status into current staging
     *
     * @param Enterprise_Staging_Model_Staging $staging
     *
     * @return Enterprise_Staging_Model_Staging_State_Website_Rollback
     */
    protected function _afterRun(Enterprise_Staging_Model_Staging $staging)
    {
        if ($staging->getId()) {
            $staging->setStatus(Enterprise_Staging_Model_Staging_Config::STATUS_COMPLETE);
        }
        return parent::_afterRun($staging);
    }
}
