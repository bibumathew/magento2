<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice API2 renderer of XML type model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Renderer_Xml implements Mage_Api2_Model_Renderer_Interface
{
    /**
     * Adapter mime type
     */
    const MIME_TYPE = 'application/xml';

    /**
     * Default name for item of non-associative array
     */
    const ARRAY_NON_ASSOC_ITEM_NAME = 'data_item';

    /**
     * Unavailable Chars which must be not used in the tag names
     *
     * @var array
     */
    protected $_unavailableChars = array(
        '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+',
        ',', '/', ';', '<', '=', '>', '?', '@', '[', '\\', ']',
        '^', '`', '{', '|', '}', '~'
    );

    /**
     * Protected pattern for check chars in the begin of tag name
     *
     * @var array
     */
    protected $_protectedTagNamePattern = '/^[0-9,.-]/';

    /**
     * Convert Array to XML
     *
     * @param array|object $data
     * @return string
     */
    public function render($data)
    {
        $data = $this->_prepareData($data, true);
        $writer = Mage::getModel('api2/renderer_xml_writer');
        $config = new Zend_Config($data);
        $writer->setConfig($config);
        return $writer->render();
    }

    /**
     * Prepare convert data
     *
     * @param $data
     * @param bool $root
     * @return array
     * @throws Exception
     */
    protected function _prepareData($data, $root = false)
    {
        if ($root && !is_array($data) && !is_object($data)) {
            $data = array($data);
        }
        if (!is_array($data) && !is_object($data)) {
            throw new Exception('Prepare data must be an object or an array.');
        }
        $data = ($data instanceof Varien_Object ? $data->toArray() : (array) $data);
        //check non associative array
        $keys = implode(array_keys($data), '');
        if ((string) (int) $keys === ltrim($keys, 0) || $keys === '0') {
            $dataLoop = $data;
            unset($data);
            $data = array(self::ARRAY_NON_ASSOC_ITEM_NAME => &$dataLoop);
            $assoc = false;
        } else {
            $dataLoop = &$data;
            $assoc = true;
        }
        foreach ($dataLoop as $key => $value) {
            if (0 === strpos($key, self::ARRAY_NON_ASSOC_ITEM_NAME)) {
                //skip processed data with renamed key name
                continue;
            }

            //process item value
            if (is_array($value) || is_object($value)) {
                //process array or object item
                $dataLoop[$key] = $this->_prepareData($value);
            } else {
                //replace "&" with HTML entity, because by default not replaced
                $dataLoop[$key] = str_replace('&', '&amp;', $value);
            }

            //process item key name
            if ($assoc) {
                if (is_numeric($key)) {
                    //tag names must not begin with the digits
                    $newKey = self::ARRAY_NON_ASSOC_ITEM_NAME . '_' . $key;
                } else {
                    //replace unavailable chars
                    $newKey = trim($key);
                    $newKey = str_replace($this->_unavailableChars, '', $newKey);
                    $newKey = str_replace(array(' ', ':'), '_', $newKey);
                    $newKey = trim($newKey, '_');

                    if (preg_match($this->_protectedTagNamePattern, $newKey)) {
                        //tag names must not begin with the digits
                        $newKey = self::ARRAY_NON_ASSOC_ITEM_NAME . '_' . $newKey;
                    }
                }
                if ($newKey !== $key) {
                    $dataLoop[$newKey] = $dataLoop[$key];
                    unset($dataLoop[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * Get MIME type generated by renderer
     *
     * @return string
     */
    public function getMimeType()
    {
        return self::MIME_TYPE;
    }
}
