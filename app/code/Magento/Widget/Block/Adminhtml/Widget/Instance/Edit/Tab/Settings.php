<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Widget
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Widget Instance Settings tab block
 *
 * @category    Magento
 * @package     Magento_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

class Settings
    extends \Magento\Adminhtml\Block\Widget\Form
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return !(bool)$this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Getter
     *
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Settings
     */
    protected function _prepareForm()
    {
        /** @var Magento_Data_Form $form */
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
            ))
        );

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>__('Settings'))
        );

        $this->_addElementTypes($fieldset);

        $fieldset->addField('type', 'select', array(
            'name'     => 'type',
            'label'    => __('Type'),
            'title'    => __('Type'),
            'required' => true,
            'values'   => $this->getTypesOptionsArray()
        ));

        /** @var $label \Magento\Core\Model\Theme\Label */
        $label = \Mage::getModel('Magento\Core\Model\Theme\Label');
        $options = $label->getLabelsCollection(__('-- Please Select --'));
        $fieldset->addField('theme_id', 'select', array(
            'name'     => 'theme_id',
            'label'    => __('Design Theme'),
            'title'    => __('Design Theme'),
            'required' => true,
            'values'   => $options
        ));
        $continueButton = $this->getLayout()
            ->createBlock('Magento\Adminhtml\Block\Widget\Button')
            ->setData(array(
                'label'     => __('Continue'),
                'onclick'   => "setSettings('" . $this->getContinueUrl() . "', 'type', 'theme_id')",
                'class'     => 'save'
            ));
        $fieldset->addField('continue_button', 'note', array(
            'text' => $continueButton->toHtml(),
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return url for continue button
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl('*/*/*', array(
            '_current' => true,
            'type'     => '{{type}}',
            'theme_id' => '{{theme_id}}'
        ));
    }

    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     */
    public function getTypesOptionsArray()
    {
        $widgets = $this->getWidgetInstance()->getWidgetsOptionArray();
        array_unshift($widgets, array(
            'value' => '',
            'label' => __('-- Please Select --')
        ));
        return $widgets;
    }

    /**
     * User-defined widgets sorting by Name
     *
     * @param array $a
     * @param array $b
     * @return boolean
     */
    protected function _sortWidgets($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }
}
