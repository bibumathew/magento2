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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_AjaxController extends Mage_Core_Controller_Front_Action {

    public function translateAction()
    {
        if (!Mage::getStoreConfigFlag('dev/locale/translate_inline')) {
            return;
        }
        if (!$translate = $this->getRequest()->getPost('translate')) {
            return;
        }

        $resource = Mage::getResourceModel('core/translate_string');
        foreach ($translate as $t) {
            $resource->saveTranslate($t['original'], $t['custom']);
        }

        exit;
    }
}
