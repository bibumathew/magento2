<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogEvent
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Events edit page
 *
 * @category   Magento
 * @package    Magento_CatalogEvent
 */
namespace Magento\CatalogEvent\Block\Adminhtml\Event;

class Edit
    extends \Magento\Adminhtml\Block\Widget\Form\Container
{
    protected $_objectId = 'id';
    protected $_blockGroup = 'Magento_CatalogEvent';
    protected $_controller = 'adminhtml_event';

    /**
     * Prepare catalog event form or category selector
     *
     * @return \Magento\CatalogEvent\Block\Adminhtml\Event\Edit
     */
    protected function _prepareLayout()
    {
        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        } else {
            $this->_addButton(
                'save_and_continue',
                array(
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute'  => array(
                        'mage-init' => array(
                            'button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'),
                        ),
                    ),
                ),
                1
            );
        }

        parent::_prepareLayout();

        if (!$this->getEvent()->getId() && !$this->getEvent()->getCategoryId()) {
            $this->setChild(
                'form',
                $this->getLayout()->createBlock(str_replace('_', '\\', $this->_blockGroup)
                    . '\\Block\\'
                    . str_replace(' ', '\\', ucwords(str_replace('_', ' ', $this->_controller . '_' . $this->_mode)))
                    . '\Category',
                    $this->getNameInLayout() . 'catalog_event_form'
                )
            );
        }

        if ($this->getRequest()->getParam('category')) {
            $this->_updateButton('back', 'label', __('Back to Category'));
        }

        if ($this->getEvent()->isReadonly() && $this->getEvent()->getImageReadonly()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            $this->_removeButton('save_and_continue');
        }

        if (!$this->getEvent()->isDeleteable()) {
            $this->_removeButton('delete');
        }

        return $this;
    }


    /**
     * Retrieve form back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('category')) {
            return $this->getUrl(
                '*/catalog_category/edit',
                array('clear' => 1, 'id' => $this->getEvent()->getCategoryId())
            );
        } elseif ($this->getEvent() && !$this->getEvent()->getId() && $this->getEvent()->getCategoryId()) {
            return $this->getUrl(
                '*/*/new',
                array('_current' => true, 'category_id' => null)
            );
        }

        return parent::getBackUrl();
    }


    /**
     * Retrieve form container header
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEvent()->getId()) {
            return __('Edit Catalog Event');
        }
        else {
            return __('Add Catalog Event');
        }
    }

    /**
     * Retrive catalog event model
     *
     * @return \Magento\CatalogEvent\Model\Event
     */
    public function getEvent()
    {
        return \Mage::registry('magento_catalogevent_event');
    }

}
