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
 * @package     Enterprise_Cms
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Cms Hierarchy Copy Form Container Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Copy extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'copy_form',
            'action'    => $this->getUrl('*/*/copy'),
            'method'    => 'post'
        ));

        $currentWebsite = $this->getRequest()->getParam('website');
        $currentStore   = $this->getRequest()->getParam('store');
        $excludeScopes = array();
        if ($currentStore) {
            $stores = Mage::app()->getStores(true, $currentStore);
            $excludeScopes = array(
                Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE . $stores[$currentStore]->getId()
            );
        } elseif ($currentWebsite) {
            $websites = Mage::app()->getWebsites(true, $currentWebsite);
            $excludeScopes = array(
                Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE . $websites[$currentWebsite]->getId()
            );
        }
        $form->addField('scopes', 'multiselect', array(
            'name'      => 'scopes[]',
            'class'     => 'copy-to-select',
            'title'     => Mage::helper('enterprise_cms')->__('Copy Hierarchy To'),
            'values'    => $this->_prepareOptions($currentWebsite, $excludeScopes)
        ));

        $form->addField('submit', 'note', array(
            'text'      => $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'   => Mage::helper('enterprise_cms')->__('Copy'),
                'class'   => 'cms-hierarchy copy-submit',
                'onclick' => 'submitCopy(this)'
            ))->toHtml(),
        ));

        if ($currentWebsite) {
            $form->addField('website', 'hidden', array(
                'name'   => 'website',
                'value' => $currentWebsite,
            ));
            if ($currentStore) {
                $form->addField('store', 'hidden', array(
                    'name'   => 'store',
                    'value' => $currentStore,
                ));
            }
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare options for Copy To select
     *
     * @param boolean $all
     * @param string $excludeScopes
     * @return array
     */
    protected function _prepareOptions($all = false, $excludeScopes)
    {
        $storeStructure = Mage::getSingleton('adminhtml/system_store')
                ->getStoresStructure($all);
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $options = array();

        foreach ($storeStructure as $website) {
            $value = Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE . $website['value'];
            if (isset($website['children'])) {
                $website['value'] = in_array($value, $excludeScopes) ? array() : $value;
                $options[] = array(
                    'label' => $website['label'],
                    'value' => $website['value'],
                    'style' => 'border-bottom: none; font-weight: bold;',
                );
                foreach ($website['children'] as $store) {
                    if (isset($store['children']) && !in_array($store['value'], $excludeScopes)) {
                        $storeViewOptions = array();
                        foreach ($store['children'] as $storeView) {
                            $storeView['value'] = Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE
                                                  . $storeView['value'];
                            if (!in_array($storeView['value'], $excludeScopes)) {
                                $storeView['label'] = str_repeat($nonEscapableNbspChar, 4) . $storeView['label'];
                                $storeViewOptions[] = $storeView;
                            }
                        }
                        if ($storeViewOptions) {
                            $options[] = array(
                                'label' => str_repeat($nonEscapableNbspChar, 4) . $store['label'],
                                'value' => $storeViewOptions
                            );
                        }
                    }
                }
            } elseif ($website['value'] == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $website['value'] = Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE
                                    . Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
                $options[] = array(
                    'label' => $website['label'],
                    'value' => $website['value'],
                );
            }
        }
        return $options;
    }
}
