<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Reminder
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Reminder rules edit tabs block
 */
class Magento_Reminder_Block_Adminhtml_Reminder_Edit_Tabs
    extends Magento_Adminhtml_Block_Widget_Tabs
{

    /**
     * Core registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Intialize form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('magento_reminder_rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Email Reminder Rule'));
    }

    /**
     * Add tab sections
     *
     * @return Magento_Reminder_Block_Adminhtml_Reminder_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'   => __('Rule Information'),
            'content' => $this->getLayout()->createBlock(
                'Magento_Reminder_Block_Adminhtml_Reminder_Edit_Tab_General',
                'adminhtml_reminder_edit_tab_general'
            )->toHtml(),
        ));

        $this->addTab('conditions_section', array(
            'label'   => __('Conditions'),
            'content' => $this->getLayout()->createBlock(
                'Magento_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Conditions',
                'adminhtml_reminder_edit_tab_conditions'
            )->toHtml()
        ));

        $this->addTab('template_section', array(
            'label'   => __('Emails and Labels'),
            'content' => $this->getLayout()->createBlock(
                'Magento_Reminder_Block_Adminhtml_Reminder_Edit_Tab_Templates',
                'adminhtml_reminder_edit_tab_templates'
            )->toHtml()
        ));

        $rule = $this->_coreRegistry->registry('current_reminder_rule');
        if ($rule && $rule->getId()) {
            $this->addTab('matched_customers', array(
                'label' => __('Matched Customers'),
                'url'   => $this->getUrl('*/*/customerGrid', array('rule_id' => $rule->getId())),
                'class' => 'ajax'
            ));
        }

        return parent::_beforeToHtml();
    }
}
