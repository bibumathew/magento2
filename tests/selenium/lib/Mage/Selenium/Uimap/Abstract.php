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
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract uimap class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Abstract
{
    /**
     * XPath string
     * @var string
     */
    protected $xPath = '';

    /**
     * UIMap elements
     * @var array
     */
    protected $_elements = array();

    /**
     * UIMap elements cache for recursive operations
     * @var array
     */
    protected $_elements_cache = array();

    /**
     * Retrieve xpath of the current element
     *
     * @return string|null
     */
    public function getXPath()
    {
        return $this->xPath;
    }

    /**
     * Retrieve all elements on current level
     * @return array
     */
    public function &getElements()
    {
        return $this->_elements;
    }

    /**
     * Parser from native UIMap array to UIMap class hierarchy
     * @param array Array with UIMap
     */
    protected function parseContainerArray(array &$container)
    {
        foreach($container as $formElemKey=>&$formElemValue) {
            if(!empty($formElemValue)) {
                $newElement = Mage_Selenium_Uimap_Factory::createUimapElement($formElemKey, $formElemValue);
                if(!empty($newElement)) {
                    if(!isset($this->_elements[$formElemKey])) {
                        $this->_elements[$formElemKey] = $newElement;
                    } else {
                        if($this->_elements[$formElemKey] instanceof ArrayObject) {
                            $this->_elements[$formElemKey].append($newElement);
                        } else {
                            //var_dump($formElemKey);
                            //var_dump($formElemValue);
                            // @TODO Some reaction?
                            //die;
                        }
                    }
                }
            }
        }
    }

    /**
     * Internal recursive function
     * @param string UIMap elements collection name
     * @param Mage_Selenium_Uimap_ElementsCollection|Mage_Selenium_Uimap_Abstract UIMap container
     * @param array Array with search results
     * @return array
     */
    protected function __getElementsRecursive($elementsCollectionName, &$container, &$cache)
    {
        foreach($container as $elKey=>&$elValue) {
            if($elValue instanceof ArrayObject) {
                if( ($elementsCollectionName == 'tabs' && $elementsCollectionName == $elKey && $elValue instanceof Mage_Selenium_Uimap_TabsCollection) ||
                    ($elementsCollectionName == 'fieldsets' && $elementsCollectionName == $elKey && $elValue instanceof Mage_Selenium_Uimap_FieldsetsCollection) ||
                    $elKey==$elementsCollectionName && $elValue instanceof Mage_Selenium_Uimap_ElementsCollection) {
                    $cache = array_merge($cache, $elValue->getArrayCopy());
                } else {
                    $this->__getElementsRecursive($elementsCollectionName, $elValue, $cache);
                }
            } elseif($elValue instanceof Mage_Selenium_Uimap_Abstract) {
                $this->__getElementsRecursive($elementsCollectionName, $elValue->getElements(), $cache);
            }
        }

        return $cache;
    }

    /**
     * Search UIMap element by name on any level from current and deeper
     * This method uses a cache to save search results
     * @param string UIMap elements collection name
     * @return array
     */
    public function getAllElements($elementsCollectionName)
    {
        if(empty($this->_elements_cache[$elementsCollectionName])) {
            $cache = array();
            $this->_elements_cache[$elementsCollectionName] = new Mage_Selenium_Uimap_ElementsCollection($elementsCollectionName,
                    $this->__getElementsRecursive($elementsCollectionName, $this->_elements, $cache));
        }

        return $this->_elements_cache[$elementsCollectionName];
    }

    /**
     * Magic method to call an accessor methods
     * @param string Format: call "get"+"UIMap properties collection name"() to get UIMap elements collection by name from current level
     *                    or "getAll"+"UIMap properties collection name"() to get UIMap elements collection by name on any level from current and deeper
     *                    or "find"+"UIMap element type"(element name) to get UIMap element by name on any level from current and deeper
     * @return Mage_Selenium_Uimap_ElementsCollection|array|Null
     */
    public function __call($name, $arguments) {
        if(preg_match('|^getAll(\w+)$|', $name)) {
            $elementName = strtolower(substr($name, 6));
            if(!empty($elementName)) {
                return $this->getAllElements($elementName);
            }
        }elseif(preg_match('|^get(\w+)$|', $name)) {
            $elementName = strtolower(substr($name, 3));
            if(!empty($elementName) && isset($this->_elements[$elementName])) {
                return $this->_elements[$elementName];
            }
        }elseif(preg_match('|^find(\w+)$|', $name)) {
            $elementsCollectionName = strtolower(substr($name, 4)) . 's';
            if(!empty($elementsCollectionName) && !empty($arguments)) {
                $elemetsColl = $this->getAllElements($elementsCollectionName);
                return $elemetsColl->get($arguments[0]);
            }
        }

        return null;
    }

}