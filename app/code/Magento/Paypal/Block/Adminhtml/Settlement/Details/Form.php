<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Settlement reports transaction details
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Block\Adminhtml\Settlement\Details;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare read-only data and group it by fieldsets
     * @return \Magento\Paypal\Block\Adminhtml\Settlement\Details\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_transaction');
        /* @var $model \Magento\Paypal\Model\Report\Settlement\Row */
        $settlement = \Mage::getSingleton('Magento\Paypal\Model\Report\Settlement');
        /* @var $settlement \Magento\Paypal\Model\Report\Settlement */

        $coreHelper = $this->helper('Magento\Core\Helper\Data');
        $fieldsets = array(
            'reference_fieldset' => array(
                'fields' => array(
                    'transaction_id' => array('label' => $settlement->getFieldLabel('transaction_id')),
                    'invoice_id' => array('label' => $settlement->getFieldLabel('invoice_id')),
                    'paypal_reference_id' => array('label' => $settlement->getFieldLabel('paypal_reference_id')),
                    'paypal_reference_id_type' => array(
                        'label' => $settlement->getFieldLabel('paypal_reference_id_type'),
                        'value' => $model->getReferenceType($model->getData('paypal_reference_id_type'))
                    ),
                    'custom_field' => array('label' => $settlement->getFieldLabel('custom_field')),
                ),
                'legend' => __('Reference Information')
            ),

            'transaction_fieldset' => array(
                'fields' => array(
                    'transaction_event_code' => array(
                        'label' => $settlement->getFieldLabel('transaction_event_code'),
                        'value' => sprintf('%s (%s)',
                            $model->getData('transaction_event_code'),
                            $model->getTransactionEvent($model->getData('transaction_event_code'))
                        )
                    ),
                    'transaction_initiation_date' => array(
                        'label' => $settlement->getFieldLabel('transaction_initiation_date'),
                        'value' => $coreHelper->formatDate(
                            $model->getData('transaction_initiation_date'),
                            \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM,
                            true
                        )
                    ),
                    'transaction_completion_date' => array(
                        'label' => $settlement->getFieldLabel('transaction_completion_date'),
                        'value' => $coreHelper->formatDate(
                            $model->getData('transaction_completion_date'),
                            \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM,
                            true
                        )
                    ),
                    'transaction_debit_or_credit' => array(
                        'label' => $settlement->getFieldLabel('transaction_debit_or_credit'),
                        'value' => $model->getDebitCreditText($model->getData('transaction_debit_or_credit'))
                    ),
                    'gross_transaction_amount' => array(
                        'label' => $settlement->getFieldLabel('gross_transaction_amount'),
                        'value' => \Mage::app()->getLocale()
                                       ->currency($model->getData('gross_transaction_currency'))
                                       ->toCurrency($model->getData('gross_transaction_amount'))
                    ),
                ),
                'legend' => __('Transaction Information')
            ),

            'fee_fieldset' => array(
                'fields' => array(
                    'fee_debit_or_credit' => array(
                        'label' => $settlement->getFieldLabel('fee_debit_or_credit'),
                        'value' => $model->getDebitCreditText($model->getData('fee_debit_or_credit'))
                    ),
                    'fee_amount' => array(
                        'label' => $settlement->getFieldLabel('fee_amount'),
                        'value' => \Mage::app()->getLocale()
                                       ->currency($model->getData('fee_currency'))
                                       ->toCurrency($model->getData('fee_amount'))
                    ),
                ),
                'legend' => __('PayPal Fee Information')
            ),
        );

        /** @var Magento_Data_Form $form */
        $form = $this->_formFactory->create();
        foreach ($fieldsets as $key => $data) {
            $fieldset = $form->addFieldset($key, array('legend' => $data['legend']));
            foreach ($data['fields'] as $id => $info) {
                $fieldset->addField($id, 'label', array(
                    'name'  => $id,
                    'label' => $info['label'],
                    'title' => $info['label'],
                    'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                ));
            }
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
