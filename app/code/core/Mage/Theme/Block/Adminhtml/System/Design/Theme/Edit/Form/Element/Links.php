<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Form element renderer to display link element
 */
class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Form_Element_Links extends Varien_Data_Form_Element_Abstract
{
    /**
     * Initialize form element
     *
     * @param array $attributes
     */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('links');
    }

    /**
     * Generates element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div id="'.$this->getHtmlId().'" ' . $this->serialize($this->getHtmlAttributes()) . '>'."\n";

        $values = $this->getValues();
        if ($values) {
            foreach ($values as $option) {
                $html .= $this->_optionToHtml($option);
            }
        }

        $html.= '</div>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Generate list of links for element content
     *
     * @param array $option
     * @return string
     */
    protected function _optionToHtml(array $option)
    {
        $allowedAttribute = array('href', 'target', 'title', 'style');
        $attributes = array();
        foreach ($option as $title => $value) {
            if (!in_array($title, $allowedAttribute)) {
                continue;
            }
            $attributes[] = $title . '="' . $this->_escape($value) . '"';
        }
        $html = '<a ' . implode(' ', $attributes) . '>';
        $html .= $this->_escape($option['label']);
        $html .= '</a>';
        $html .= isset($option['delimiter']) ? $option['delimiter'] : '';
        return $html;
    }

    /**
     * Prepare array of anchor attributes
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        return array('rel', 'rev', 'accesskey', 'class', 'style', 'tabindex', 'onmouseover',
                     'title', 'xml:lang', 'onblur', 'onclick', 'ondblclick', 'onfocus', 'onmousedown',
                     'onmousemove', 'onmouseout', 'onmouseup', 'onkeydown', 'onkeypress', 'onkeyup');
    }
}
