<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Integration
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Main Integration info edit form
 *
 * @category   Magento
 * @package    Magento_Integration
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**#@+
     * Form elements names.
     */
    const HTML_ID_PREFIX = 'integration_properties_';
    const DATA_ID = 'integration_id';
    const DATA_NAME = 'name';
    const DATA_EMAIL = 'email';
    const DATA_ENDPOINT = 'endpoint';
    const DATA_SETUP_TYPE = 'setup_type';
    const DATA_CONSUMER_ID = 'consumer_id';
    /**#@-*/

    /**
     * Set form id prefix, declare fields for integration info
     *
     * @return \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(self::HTML_ID_PREFIX);
        $integrationData = $this->_coreRegistry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION);
        $this->_addGeneralFieldset($form, $integrationData);
        $this->_addDetailsFieldset($form, $integrationData);
        $form->setValues($integrationData);
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Integration Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
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
     * Add fieldset with general integration information.
     *
     * @param \Magento\Data\Form $form
     * @param array $integrationData
     */
    protected function _addGeneralFieldset($form, $integrationData)
    {
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));

        $disabled = false;
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset->addField(self::DATA_ID, 'hidden', array('name' => 'id'));

            if ($integrationData[self::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                $disabled = true;
            }
        }

        $fieldset->addField(
            self::DATA_NAME,
            'text',
            array(
                'label' => __('Name'),
                'name' => self::DATA_NAME,
                'required' => true,
                'disabled' => $disabled,
                'maxlength' => '255'
            )
        );
        $fieldset->addField(
            self::DATA_EMAIL,
            'text',
            array(
                'label' => __('Email'),
                'name' => self::DATA_EMAIL,
                'disabled' => $disabled,
                'class' => 'validate-email',
                'maxlength' => '254'
            )
        );
        $fieldset->addField(
            self::DATA_ENDPOINT,
            'text',
            array(
                'label' => __('Callback URL'),
                'name' => self::DATA_ENDPOINT,
                'disabled' => $disabled,
                // @codingStandardsIgnoreStart
                'note' => __(
                    'Enter URL where Oauth credentials can be sent when using Oauth for token exchange. We strongly recommend using https://.'
                )
                // @codingStandardsIgnoreEnd
            )
        );
    }

    /**
     * Add fieldset with integration details. This fieldset is available for existing integrations only.
     *
     * @param \Magento\Data\Form $form
     * @param array $integrationData
     */
    protected function _addDetailsFieldset($form, $integrationData)
    {
        if (isset($integrationData[self::DATA_ID])) {
            $fieldset = $form->addFieldset('details_fieldset', array('legend' => __('Integration Details')));
            /** @var \Magento\Integration\Block\Adminhtml\Integration\Tokens $tokensBlock */
            $tokensBlock = $this->getChildBlock('integration_tokens');
            foreach ($tokensBlock->getFormFields() as $field) {
                $fieldset->addField($field['name'], $field['type'], $field['metadata']);
            }
        }
    }
}
