<?php

/**
 * Base class for simplexml based configurations
 *
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @package     Varien
 * @subpackage  Simplexml
 * @author      Moshe Gurvich <moshe@varien.com>
 */
class Varien_Simplexml_Config
{
    /**
     * Configuration xml
     *
     * @var Varien_Simplexml_Element
     */
    protected $_xml = null;
    
    /**
     * Cache resource object
     *
     * @var Varien_Simplexml_Config_Cache_Abstract
     */
    protected $_cache = null;

    /**
     * Class name of simplexml elements for this configuration
     *
     * @var string
     */
    protected $_elementClass = 'Varien_Simplexml_Element';
    
    /**
     * Xpath describing nodes in configuration that need to be extended
     * 
     * @example <allResources extends="/config/modules//resource"/>
     */
    protected $_xpathExtends = "//*[@extends]";

    /**
     * Constructor
     * 
     * Initializes XML for this configuration
     *
     * @see self::setXml
     * @param string|Varien_Simplexml_Element $sourceData
     * @param string $sourceType
     */
    public function __construct($sourceData='', $sourceType='') {
        $this->setXml($sourceData, $sourceType);
        $this->_cache = new Varien_Simplexml_Config_Cache_File();
        $this->_cache->setConfig($this);
    }

    /**
     * Returns whether the config was loaded from cache
     *
     * @return boolean
     */
    public function isCacheLoaded()
    {
        return $this->_cacheLoaded;
    }

    /**
     * Sets xml for this configuration
     *
     * If $sourceType is not specified will try to recognize type of $sourceData
     * 
     * Possible cases:
     * - xml: $sourceData is a Varien_Simplexml_Element instance
     * - dom: $sourceData is a DOM element
     * - file: xml will be imported from file
     * - string: xml will be imported from XML string
     * 
     * @param string|Varien_Simplexml_Element $sourceData
     * @param string $sourceType
     * @return Varien_Simplexml_Config
     */
    public function setXml($sourceData, $sourceType='')
    {
        if (''===$sourceType) {
            if ($sourceData instanceof SimpleXMLElement) {
                $sourceType = 'xml';
            } elseif ($sourceData instanceof DomNode) {
                $sourceType = 'dom';
            } elseif (is_string($sourceData)) {
                if (strlen($sourceData)<1000 && is_readable($sourceData)) {
                    $sourceType = 'file';
                } else {
                    $sourceType = 'string';
                }
            }
        }

        switch ($sourceType) {
            case 'xml':
                $this->_xml = $sourceData;
                break;

            case 'dom':
                $this->_xml = $this->loadDom($sourceData);
                break;

            case 'file':
                $this->_xml = $this->loadFile($sourceData);
                break;

            case 'string':
                $this->_xml = $this->loadString($sourceData);
                break;
        }
        return $this;
    }
    
    /**
     * Returns node found by the $path
     *
     * @see     Varien_Simplexml_Element::descend
     * @param   string $path
     * @return  Varien_Simplexml_Element
     */
    public function getNode($path=null)
    {
        if (empty($this->_xml)) {
            return false;
        } elseif (empty($path)) {
            return $this->_xml;
        } else {
            return $this->_xml->descend($path);
        }
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return array
     */
    public function getXpath($xpath)
    {
        if (empty($this->_xml)) {
            return false;
        }

        if (!$result = @$this->_xml->xpath($xpath)) {
            return false;
        }

        return $result;
    }
    
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Imports XML file
     *
     * @param string $filePath
     * @return Varien_Simplexml_Element
     */
    public function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            throw new Exception('Can not read xml file '.$filePath);
        }

        $fileData = file_get_contents($filePath);
        $fileData = $this->_processFileData($fileData);
        $xml = $this->loadString($fileData, $this->_elementClass);

        return $xml;
    }

    /**
     * Imports XML string
     *
     * @param string $string
     * @return Varien_Simplexml_Element
     */
    public function loadString($string)
    {
        $xml = simplexml_load_string($string, $this->_elementClass);

        return $xml;
    }

    /**
     * Imports DOM node
     *
     * @param DOMNode $dom
     * @return Varien_Simplexml_Element
     */
    public function loadDom($dom)
    {
        $xml = simplexml_import_dom($dom, $this->_elementClass);

        return $xml;
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param boolean $overwrite
     * @return Varien_Simplexml_Config
     */
    public function setNode($path, $value, $overwrite=true)
    {
        $arr1 = explode('/', $path);
        $arr = array();
        foreach ($arr1 as $v) {
            if (!empty($v)) $arr[] = $v;
        }
        $last = sizeof($arr)-1;
        $xml = $this->_xml;
        foreach ($arr as $i=>$nodeName) {
            if ($last===$i) {
                if (!isset($xml->$nodeName) || $overwrite) {
                    $xml->$nodeName = $value;
                }
            } else {
                if (!isset($xml->$nodeName)) {
                    $xml = $xml->addChild($nodeName);
                } else {
                    $xml = $xml->$nodeName;
                }
            }

        }
        return $this;
    }

    /**
     * Nicely ident resulting XML file
     *
     * @param string $filePath
     * @return Varien_Simplexml_Config
     */
    public function saveFile($filePath)
    {
        file_put_contents($filePath, $this->_xml->asNiceXml());
        return $this;
    }

    /**
     * Process configuration xml
     *
     * @return Varien_Simplexml_Config
     */
    public function applyExtends()
    {
        $targets = $this->getXpath($this->_xpathExtends);
        if (!$targets) {
            return $this;
        }

        foreach ($targets as $target) {
            $sources = $this->getXpath((string)$target['extends']);
            if ($sources) {
                foreach ($sources as $source) {
                    $target->extend($source);
                }
            } else {
                #echo "Not found extend: ".(string)$target['extends'];
            }
            #unset($target['extends']);
        }
        return $this;
    }

    /**
     * Stub method for processing file data right after loading the file text
     *
     * @param string $text
     * @return string
     */
    protected function _processFileData($text)
    {
        return $text;
    }
}
