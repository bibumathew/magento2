<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $model = Mage::registry('current_rma');

        if ($model) {
            if ($model->getId()) {
                $form->addField('entity_id', 'hidden', array(
                    'name' => 'entity_id',
                ));
                $form->setValues($model->getData());
            }

            $this->_order = ($model->getOrderId());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}