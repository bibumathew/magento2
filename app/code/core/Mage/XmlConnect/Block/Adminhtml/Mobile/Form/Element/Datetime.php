<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * XmlConnect data selector form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Datetime
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * Date
     *
     * @var Zend_Date
     */
    protected $_value;

    protected function _construct($attributes=array())
    {
        parent::_construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }
    }

    /**
     * If script executes on x64 system, converts large
     * numeric values to timestamp limit
     *
     * @param int $value
     * @return int
     */
    protected function _toTimestamp($value)
    {
        $value = (int)$value;
        if ($value > 3155760000) {
            $value = 0;
        }
        return $value;
    }

    /**
     * Set date value
     * If Zend_Date instance is provided instead of value, other params will be ignored.
     * Format and locale must be compatible with Zend_Date
     *
     * @param mixed $value
     * @param string $format
     * @param string $locale
     * @return Varien_Data_Form_Element_Date
     */
    public function setValue($value, $format = null, $locale = null)
    {
        if (empty($value)) {
            $this->_value = '';
            return $this;
        }
        if ($value instanceof Zend_Date) {
            $this->_value = $value;
            return $this;
        }
        if (preg_match('/^[0-9]+$/', $value)) {
            $this->_value = new Zend_Date($this->_toTimestamp($value));
            //$this->_value = new Zend_Date((int)value);
            return $this;
        }
        // last check, if input format was set
        if (null === $format) {
            $format = Varien_Date::DATETIME_INTERNAL_FORMAT;
            if ($this->getInputFormat()) {
                $format = $this->getInputFormat();
            }
        }
        // last check, if locale was set
        if (null === $locale) {
            if (!$locale = $this->getLocale()) {
                $locale = null;
            }
        }
        try {
            $this->_value = new Zend_Date($value, $format, $locale);
        } catch (Exception $e) {
            $this->_value = '';
        }
        return $this;
    }

    /**
     * Get date value as string.
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with Zend_Date)
     * @return string
     */
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getFormat() . " " . $this->getFormatT();
        }
        return $this->_value->toString($format);
    }

    /**
     * Get value instance, if any
     *
     * @return Zend_Date
     */
    public function getValueInstance()
    {
        if (empty($this->_value)) {
            return null;
        }
        return $this->_value;
    }

    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (Zend_Date)
     * - output format must be set (compatible with Zend_Date)
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s style="width:110px !important;" />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes())
        );
        $outputFormat = $this->getFormat();
        $outputTimeFormat = $this->getFormatT();
        if (empty($outputFormat)) {
            Mage::throwException(
                $this->__('Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().')
            );
        }

        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
            (function($) {
                $("#%s").calendar({
                    buttonImage: "%s",
                    buttonText: "%s",
                    dateFormat: "%s",
                    timeFormat: "%s",
                    showsTime: %s
                })
            })(jQuery);
            //]]>
            </script>',
            $this->getHtmlId(),
            $this->getImage(),
            $this->__('Select Date'),
            $outputFormat,
            $outputTimeFormat ?: '',
            $this->getTime() ? 'true' : 'false'
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
