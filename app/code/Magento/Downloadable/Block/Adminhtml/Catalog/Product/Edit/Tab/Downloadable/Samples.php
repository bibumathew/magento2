<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml catalog product downloadable items tab links section
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class Samples
    extends \Magento\Adminhtml\Block\Widget
{
    /**
     * Block config data
     *
     * @var \Magento\Object
     */
    protected $_config;

    protected $_template = 'product/edit/downloadable/samples.phtml';

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManager $storeManager,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Get model of the product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return \Mage::registry('current_product');
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
         return $this->getProduct()->getDownloadableReadonly();
    }


    /**
     * Retrieve Add Button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $addButton = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
            ->setData(array(
                'label' => __('Add New Row'),
                'id' => 'add_sample_item',
                'class' => 'add',
            ));
        return $addButton->toHtml();
    }

    /**
     * Retrieve samples array
     *
     * @return array
     */
    public function getSampleData()
    {
        $samplesArr = array();
        if ($this->getProduct()->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $samplesArr;
        }
        $samples = $this->getProduct()->getTypeInstance()->getSamples($this->getProduct());
        $fileHelper = \Mage::helper('Magento\Downloadable\Helper\File');
        foreach ($samples as $item) {
            $tmpSampleItem = array(
                'sample_id' => $item->getId(),
                'title' => $this->escapeHtml($item->getTitle()),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder(),
            );
            $file = $fileHelper->getFilePath(
                \Magento\Downloadable\Model\Sample::getBasePath(), $item->getSampleFile()
            );
            if ($item->getSampleFile() && !is_file($file)) {
                \Mage::helper('Magento\Core\Helper\File\Storage\Database')->saveFileToFilesystem($file);
            }
            if ($item->getSampleFile() && is_file($file)) {
                $tmpSampleItem['file_save'] = array(
                    array(
                        'file' => $item->getSampleFile(),
                        'name' => $fileHelper->getFileFromPathFile($item->getSampleFile()),
                        'size' => filesize($file),
                        'status' => 'old'
                    ));
            }
            if ($this->getProduct() && $item->getStoreTitle()) {
                $tmpSampleItem['store_title'] = $item->getStoreTitle();
            }
            $samplesArr[] = new \Magento\Object($tmpSampleItem);
        }

        return $samplesArr;
    }

    /**
     * Check exists defined samples title
     *
     * @return bool
     */
    public function getUsedDefault()
    {
        return $this->getProduct()->getAttributeDefaultValue('samples_title') === false;
    }

    /**
     * Retrieve Default samples title
     *
     * @return string
     */
    public function getSamplesTitle()
    {
        return $this->getProduct()->getId() && $this->getProduct()->getTypeId() == 'downloadable'
            ? $this->getProduct()->getSamplesTitle()
            : \Mage::getStoreConfig(\Magento\Downloadable\Model\Sample::XML_PATH_SAMPLES_TITLE);
    }

    /**
     * Prepare layout
     *
     */
    protected function _prepareLayout()
    {
        $this->addChild('upload_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'id'      => '',
            'label'   => __('Upload Files'),
            'type'    => 'button',
            'onclick' => 'Downloadable.massUploadByType(\'samples\')'
        ));
    }

    /**
     * Retrieve Upload button HTML
     *
     * @return string
     */
    public function getUploadButtonHtml()
    {
        return $this->getChildBlock('upload_button')->toHtml();
    }

    /**
     * Retrieve config json
     *
     * @return string
     */
    public function getConfigJson()
    {
        $this->getConfig()->setUrl(\Mage::getModel('Magento\Backend\Model\Url')
            ->addSessionParam()
            ->getUrl('*/downloadable_file/upload', array('type' => 'samples', '_secure' => true)));
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('samples');
        $this->getConfig()->setFilters(array(
            'all'    => array(
                'label' => __('All Files'),
                'files' => array('*.*')
            )
        ));
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return \Mage::helper('Magento\Core\Helper\Data')->jsonEncode($this->getConfig()->getData());
    }

    /**
     * Retrieve config object
     *
     * @return \Magento\Object
     */
    public function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = new \Magento\Object();
        }

        return $this->_config;
    }

    /**
     * Get is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
