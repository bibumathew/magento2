<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Magento_Data
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Form time element
 *
 * @category   Magento
 * @package    Magento_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Data\Form\Element;

class Time extends \Magento\Data\Form\Element\AbstractElement
{
    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Data_Form_Element_Factory $factoryElement
     * @param Magento_Data_Form_Element_CollectionFactory $factoryCollection
     * @param array $attributes
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Data_Form_Element_Factory $factoryElement,
        Magento_Data_Form_Element_CollectionFactory $factoryCollection,
        $attributes = array()
    ) {
        parent::__construct($coreData, $factoryElement, $factoryCollection, $attributes);
        $this->setType('time');
    }

    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name.= '[]';
        }
        return $name;
    }

    public function getElementHtml()
    {
        $this->addClass('select');

        $value_hrs = 0;
        $value_min = 0;
        $value_sec = 0;

        if( $value = $this->getValue() ) {
            $values = explode(',', $value);
            if( is_array($values) && count($values) == 3 ) {
                $value_hrs = $values[0];
                $value_min = $values[1];
                $value_sec = $values[2];
            }
        }

        $html = '<input type="hidden" id="' . $this->getHtmlId() . '" ' . $this->_getUiId() . '/>';
        $html .= '<select name="'. $this->getName() . '" style="width:50px" '.$this->serialize($this->getHtmlAttributes()) . $this->_getUiId('hour') . '>'."\n";
        for( $i=0;$i<24;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_hrs == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";

        $html.= ':&nbsp;<select name="'. $this->getName() . '" style="width:50px" '.$this->serialize($this->getHtmlAttributes()) . $this->_getUiId('minute') . '>'."\n";
        for( $i=0;$i<60;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_min == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";

        $html.= ':&nbsp;<select name="'. $this->getName() . '" style="width:50px" '.$this->serialize($this->getHtmlAttributes()) . $this->_getUiId('second') . '>'."\n";
        for( $i=0;$i<60;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_sec == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}
