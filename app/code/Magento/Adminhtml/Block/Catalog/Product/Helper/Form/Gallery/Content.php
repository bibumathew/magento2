<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog product form gallery content
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Data\Form\Element\AbstractElement getElement()
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Gallery;

class Content extends \Magento\Adminhtml\Block\Widget
{
    protected $_template = 'catalog/product/helper/gallery.phtml';

    protected function _prepareLayout()
    {
        $this->addChild('uploader', 'Magento\Adminhtml\Block\Media\Uploader');

        $this->getUploader()->getConfig()
            ->setUrl(
                \Mage::getModel('Magento\Backend\Model\Url')
                    ->addSessionParam()
                    ->getUrl('adminhtml/catalog_product_gallery/upload')
            )
            ->setFileField('image')
            ->setFilters(array(
                'images' => array(
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                )
            ));

        \Mage::dispatchEvent('catalog_product_gallery_prepare_layout', array('block' => $this));

        return parent::_prepareLayout();
    }


    /**
     * Retrive uploader block
     *
     * @return \Magento\Adminhtml\Block\Media\Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrive uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    public function getImagesJson()
    {
        if (is_array($this->getElement()->getValue())) {
            $value = $this->getElement()->getValue();
            if (is_array($value['images']) && count($value['images']) > 0) {
                foreach ($value['images'] as &$image) {
                    $image['url'] = \Mage::getSingleton('Magento\Catalog\Model\Product\Media\Config')
                        ->getMediaUrl($image['file']);
                }
                return \Mage::helper('Magento\Core\Helper\Data')->jsonEncode($value['images']);
            }
        }
        return '[]';
    }

    public function getImagesValuesJson()
    {
        $values = array();
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $values[$attribute->getAttributeCode()] = $this->getElement()->getDataObject()->getData(
                $attribute->getAttributeCode()
            );
        }
        return \Mage::helper('Magento\Core\Helper\Data')->jsonEncode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = array();
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imageTypes[$attribute->getAttributeCode()] = array(
                'code' => $attribute->getAttributeCode(),
                'value' => $this->getElement()->getDataObject()->getData($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name' => $this->getElement()->getAttributeFieldName($attribute)
            );
        }
        return $imageTypes;
    }

    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if($this->getElement()->canDisplayUseDefault($attribute))  {
                return true;
            }
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        return $this->getElement()->getDataObject()->getMediaAttributes();
    }

    public function getImageTypesJson()
    {
        return \Mage::helper('Magento\Core\Helper\Data')->jsonEncode($this->getImageTypes());
    }

}
