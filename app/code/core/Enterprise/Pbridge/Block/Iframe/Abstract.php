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
 * @package     Enterprise_Pbridge
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Abstract payment block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Pbridge_Block_Iframe_Abstract extends Mage_Payment_Block_Form
{
    /**
     * Default iframe width
     *
     * @var string
     */
    protected $_iframeWidth = '100%';

    /**
     * Default iframe height
     *
     * @var string
     */
    protected $_iframeHeight = '350';

    /**
     * Default iframe block type
     *
     * @var string
     */
    protected $_iframeBlockType = 'core/template';

    /**
     * Default iframe template
     *
     * @var string
     */
    protected $_iframeTemplate = 'pbridge/iframe.phtml';

    /**
     * Whether scrolling enabled for iframe element, auto or not
     *
     * @var string
     */
    protected $_iframeScrolling = 'no';

    /**
     * Whether to allow iframe body reloading
     *
     * @var bool
     */
    protected $_allowReload = true;

    /**
     * Return redirect url for Payment Bridge application
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getUrl('enterprise_pbridge/pbridge/result', array('_current' => true, '_secure' => true));
    }

    /**
     * Getter.
     * Return Payment Bridge url with required parameters (such as merchant code, merchant key etc.)
     *
     */
    abstract public function getSourceUrl();

    /**
     * Create and return iframe block
     *
     * @return Mage_Core_Block_Template
     */
    public function getIframeBlock()
    {
        $iframeBlock = $this->getLayout()->createBlock($this->_iframeBlockType)
            ->setTemplate($this->_iframeTemplate)
            ->setScrolling($this->_iframeScrolling)
            ->setIframeWidth($this->_iframeWidth)
            ->setIframeHeight($this->_iframeHeight)
            ->setSourceUrl($this->getSourceUrl());
        return $iframeBlock;
    }

    /**
     * Returns config options for PBridge iframe block
     *
     * @param string $param
     * @return string
     */
    public function getFrameParam($param = '')
    {
        return Mage::getStoreConfig('payment_services/pbridge_styling/' . $param);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setChild('pbridge_iframe', $this->getIframeBlock());
        return parent::_toHtml();
    }

    /**
     * Returns merged css url of saas for pbridge
     *
     * @return string
     */
    public function getCssUrl()
    {
        if (!$this->getFrameParam('use_theme')) {
            return '';
        }
        $shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
        if (!is_object($this->getLayout()->getBlock('head'))) {
            return Mage::getSingleton('Enterprise_Pbridge_Model_Session')->getCssUrl();
        }
        $items = $this->getLayout()->getBlock('head')->getData('items');
        $lines  = array();
        foreach ($items as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
                continue;
            }
            if (!empty($item['if'])) {
                continue;
            }
            if (strstr($item['params'], "all")) {
                if ($item['type'] == 'skin_css' || $item['type'] == 'js_css') {
                    $lines[$item['type']][$item['params']][$item['name']] = $item['name'];
                }
            }
        }
        if (!empty($lines)) {
            $url = $this->_prepareCssElements(
                empty($lines['js_css']) ? array() : $lines['js_css'],
                empty($lines['skin_css']) ? array() : $lines['skin_css'],
                $shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
            );
        }
        Mage::getSingleton('Enterprise_Pbridge_Model_Session')->setCssUrl($url);
        return $url;
    }

    /**
     * Merge css array into one url
     *
     * @param array $staticItems
     * @param array $skinItems
     * @param null $mergeCallback
     * @return string
     */
    protected function _prepareCssElements(array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? Mage::getBaseDir() . DS . 'js' . DS . $name : $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback ? $designPackage->getFilename($name, array('_type' => 'skin'))
                    : $designPackage->getSkinUrl($name, array());
            }
        }

        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $url[] = $mergedUrl;
            } else {
                foreach ($rows as $src) {
                    $url[] = $src;
                }
            }
        }
        return $url[0];
    }

    /**
     * Generate unique identifier for current merchant and customer
     *
     *
     * @internal param $storeId
     * @return null|string
     */
    public function getCustomerIdentifier()
    {
        $customer = $this->_getCurrentCustomer();
        if ($customer && $customer->getEmail()) {
            return Mage::helper('Enterprise_Pbridge_Helper_Data')->getCustomerIdentifierByEmail($customer->getEmail());
        }
        return null;
    }

    /**
     * Get current customer object
     *
     * @return null|Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
        }

        return null;
    }
}