<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Product options text type block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Block_Product_View_Options_Type_Select
    extends Magento_Catalog_Block_Product_View_Options_Abstract
{
    /**
     * Return html for control element
     *
     * @return string
     */
    public function getValuesHtml()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();

        $this->setSkipJsReloadPrice(1); // Remove inline prototype onclick and onchange events

        if ($_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('Magento_Core_Block_Html_Select')
                ->setData(array(
                    'id' => 'select_'.$_option->getId(),
                    'class' => $require.' product-custom-option'
                ));
            if ($_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options['.$_option->getid().']')
                    ->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options['.$_option->getid().'][]');
                $select->setClass('multiselect'.$require.' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . $priceStr . '',
                    array(
                        'price' => $this->helper('Magento_Core_Helper_Data')->currencyByStore(
                            $_value->getPrice(true),
                            $store,
                            false
                        )
                    )
                );
            }
            if ($_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
            || $_option->getType() == Magento_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
            ) {
            $selectHtml = '<div class="options-list nested" id="options-'.$_option->getId().'-list">';
            $require = ($_option->getIsRequire()) ? ' required' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case Magento_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice"><input type="radio" id="options_' . $_option->getId() . '" class="'
                            . $class . ' product-custom-option" name="options[' . $_option->getId() . ']"'
                            . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                            . ' value="" checked="checked" /><label class="label" for="options_'
                            . $_option->getId() . '"><span>' . __('None') . '</span></label></div>';
                    }
                    break;
                case Magento_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;

                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent')
                ));

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $selectHtml .= '<div class="field choice '. $require .'">' . '<input type="' . $type . '" class="' . $class . ' '
                    . $require . ' product-custom-option"'
                    . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                    . ' name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId()
                    . '_' . $count . '" value="' . $htmlValue . '" ' . $checked . ' price="'
                    . $this->helper('Magento_Core_Helper_Data')->currencyByStore($_value->getPrice(true), $store, false) . '" />'
                    . '<label class="label" for="options_' . $_option->getId() . '_' . $count . '"><span>'
                    . $_value->getTitle() . '</span>' . $priceStr . '</label>';
                $selectHtml .= '</div>';
            }
            $selectHtml .= '</div>';

            return $selectHtml;
        }
    }

}
