<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Tag
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog product rss link builder class
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Tag_Block_Catalog_Product_Rss_Link extends Mage_Core_Block_Template
{
    /**
     * Keep true in cases when rss feed enabled for tagged products
     *
     * @var bool
     */
    protected $_isRssEnabled;

    /**
     * Id of tag
     *
     * @var int
     */
    protected $_tagId;

    /**
     * @var Mage_Tag_Model_Tag
     */
    protected $_tagModel;

    /**
     * @var Mage_Core_Model_Url
     */
    protected $_coreUrlModel;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        if (isset($data['rss_catalog_tag_enabled'])) {
            $this->_isRssEnabled = $data['rss_catalog_tag_enabled'];
        } else {
            $this->_isRssEnabled = Mage::getStoreConfig('rss/catalog/tag');
        }

        if (isset($data['tag_id'])) {
            $this->_tagId = $data['tag_id'];
        } else {
            $this->_tagId = $this->getRequest()->getParam('tagId');
        }

        if (isset($data['tag_model'])) {
            $this->_tagModel = $data['tag_model'];
        } else {
            $this->_tagModel = Mage::getModel('Mage_Tag_Model_Tag');
        }

        if (isset($data['core_url_model'])) {
            $this->_coreUrlModel = $data['core_url_model'];
        } else {
            $this->_coreUrlModel = Mage::getModel('Mage_Core_Model_Url');
        }
    }

    /**
     * Retrieve link on product rss feed tagged with loaded tag
     *
     * @return bool|string
     */
    public function getLinkUrl()
    {
        if ($this->_isRssEnabled && $this->_tagId) {
            /** @var $tagModel Mage_Tag_Model_Tag */
            $this->_tagModel->load($this->_tagId);
            if ($this->_tagModel && $this->_tagModel->getId()) {
                return $this->_coreUrlModel->getUrl('rss/catalog/tag',
                    array('tagName' => urlencode($this->_tagModel->getName()))
                );
            }
        }

        return false;
    }
}
