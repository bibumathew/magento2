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
 * XmlConnect fixed Varien SimpleXML Element class
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Element extends Varien_Simplexml_Element
{
    /**
     * Appends $source to current node
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $source
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function appendChild($source)
    {
        if (sizeof($source->children())) {
            $name  = $source->getName();
            $child = $this->addChild($name);
        } else {
            $child = $this->addChild($source->getName(), $this->xmlentities($source));
        }
        $child->setParent($this);

        $attributes = $source->attributes();
        foreach ($attributes as $key=>$value) {
            $child->addAttribute($key, $this->xmlAttribute($value));
        }

        foreach ($source->children() as $sourceChild) {
            $child->appendChild($sourceChild);
        }
        return $this;
    }

    /**
     * Escape xml entities
     *
     * @param mixed $data
     * @param bool $stripTags
     * @param array $allowedTags
     * @return mixed
     */
    public function escapeXml($data, $stripTags = true, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                if ($stripTags) {
                    $item = Mage::helper('Mage_Core_Helper_Data')->stripTags($item, $allowedTags);
                }
                $result[] = $this->xmlentities($item);
            }
        } else {
            if (is_null($data)) {
                $data = $this;
            }
            $data = (string)$data;

            if ($stripTags) {
                $data = Mage::helper('Mage_Core_Helper_Data')->stripTags($data, $allowedTags);
            }
            $result = $this->xmlentities($data);
        }
        return $result;
    }

    /**
     * Converts meaningful xml character (") to xml attribute specification
     *
     * @param string $value
     * @param bool $stripTags
     * @return string|this
     */
    public function xmlAttribute($value = null, $stripTags = true)
    {
        if (is_null($value)) {
            $value = $this;
        }
        $value = (string)$value;

        if ($stripTags) {
            $value = Mage::helper('Mage_Core_Helper_Data')->stripTags($value);
        }
        $value = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $value);
        return $value;
    }

    /**
     * Add field to fieldset
     *
     * @param string $elementName
     * @param string $elementType
     * @param array $config
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addField($elementName, $elementType, array $config)
    {
        $newField = $this->addChild('field');
        $newField->addAttribute('name', $this->xmlAttribute($elementName));
        $newField->addAttribute('type', $this->xmlAttribute($elementType));
        foreach ($config as $key => $val) {
            $newField->addAttribute($key, $this->xmlAttribute($val));
        }
        return $newField;
    }

    /**
     * Add custom field to SimpleXML element
     *
     * @param string $childName
     * @param string $value
     * @param array $config
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addCustomChild($childName, $value = null, $config = null)
    {
        $customFiled = $this->addChild($childName, $this->xmlentities($value));

        if (is_array($config)) {
            foreach ($config as $key => $val) {
                $customFiled->addAttribute($key, $this->xmlAttribute($val));
            }
        }
        return $customFiled;
    }
}
