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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


$cmsBlock = array(
    'title'         => 'Catalog Events Lister',
    'identifier'    => 'catalog_events_lister',
    'content'       => '{{block type="enterprise_catalogevent/event_lister" name="catalog.event.lister" template="catalogevent/lister.phtml"}}',
    'is_active'     => 1,
    'stores'        => 0,
);

Mage::getModel('cms/block')->setData($cmsBlock)->save();