<?php
use Zend\Code\Reflection\ClassReflection;

/**
 * Config reader properties type reader.
 *
 * @copyright {}
 */
class Mage_Webapi_Model_Config_Reader_TypeProcessor
{
    /**
     * @var Mage_Webapi_Helper_Data
     */
    protected $_helper;

    /**
     * @var Magento_Autoload
     */
    protected $_autoload;

    /**
     * Array of types data.
     *
     * @var array
     */
    protected $_types;

    /**
     * Types class map.
     *
     * @var array
     */
    protected $_typeToClassMap;

    public function __construct(Mage_Webapi_Helper_Data $helper)
    {
        $this->_helper = $helper;
        $this->_autoload = Magento_Autoload::getInstance();
    }

    /**
     * Retrieve processed types data.
     *
     * @return array
     */
    public function getTypesData()
    {
        return $this->_types;
    }

    /**
     * Retrieve processed types to class map.
     *
     * @return array
     */
    public function getTypeToClassMap()
    {
        return $this->_typeToClassMap;
    }

    /**
     * Process type name.
     * In case parameter type is a complex type (class) - process its properties.
     *
     * @param string $type
     * @return string
     */
    public function process($type)
    {
        $typeName = $this->_helper->normalizeType($type);
        if (!$this->_helper->isTypeSimple($typeName)) {
            $complexTypeName = $this->_helper->translateTypeName($type);
            if (!isset($this->_types[$complexTypeName])) {
                $this->_processComplexType($type);
                if (!$this->_helper->isArrayType($complexTypeName)) {
                    $this->_typeToClassMap[$complexTypeName] = $type;
                }
            }
            $typeName = $complexTypeName;
        }

        return $typeName;
    }

    /**
     * Retrieve complex type information from class public properties.
     *
     * @param string $class
     * @return array
     * @throws InvalidArgumentException
     */
    protected function _processComplexType($class)
    {
        $typeName = $this->_helper->translateTypeName($class);
        $this->_types[$typeName] = array();
        if ($this->_helper->isArrayType($class)) {
            $this->process($this->_helper->getArrayItemType($class));
        } else {
            if (!$this->_autoload->classExists($class)) {
                throw new InvalidArgumentException(sprintf('Could not load the "%s" class as parameter type.', $class));
            }
            $reflection = new ClassReflection($class);
            $docBlock = $reflection->getDocBlock();
            $this->_types[$typeName]['documentation'] = $docBlock ? $this->_getDescription($docBlock) : '';
            $defaultProperties = $reflection->getDefaultProperties();
            /** @var \Zend\Code\Reflection\PropertyReflection $property */
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $this->_processProperty($property, $defaultProperties, $typeName);
            }
        }

        return $this->_types[$typeName];
    }

    /**
     * Process class property.
     *
     * @param Zend\Code\Reflection\PropertyReflection $property
     * @param $defaultProperties
     * @param $typeName
     * @throws InvalidArgumentException
     */
    protected function _processProperty(
        \Zend\Code\Reflection\PropertyReflection $property,
        $defaultProperties,
        $typeName
    ) {
        $propertyName = $property->getName();
        $propertyDocBlock = $property->getDocBlock();
        if (!$propertyDocBlock) {
            throw new InvalidArgumentException('Each property must have description with @var annotation.');
        }
        $varTags = $propertyDocBlock->getTags('var');
        if (empty($varTags)) {
            throw new InvalidArgumentException('Property type must be defined with @var tag.');
        }
        /** @var \Zend\Code\Reflection\DocBlock\Tag\GenericTag $varTag */
        $varTag = current($varTags);
        $varContentParts = explode(' ', $varTag->getContent(), 2);
        $varType = current($varContentParts);
        $varInlineDoc = (count($varContentParts) > 1) ? end($varContentParts) : '';
        $optionalTags = $propertyDocBlock->getTags('optional');
        if (!empty($optionalTags)) {
            /** @var \Zend\Code\Reflection\DocBlock\Tag\GenericTag $isOptionalTag */
            $isOptionalTag = current($optionalTags);
            $isOptional = $isOptionalTag->getName() == 'optional';
        } else {
            $isOptional = false;
        }

        $this->_types[$typeName]['parameters'][$propertyName] = array(
            'type' => $this->process($varType),
            'required' => !$isOptional && is_null($defaultProperties[$propertyName]),
            'default' => $defaultProperties[$propertyName],
            'documentation' => $varInlineDoc . $this->_getDescription($propertyDocBlock)
        );
    }

    /**
     * Get short and long description from docblock and concatenate.
     *
     * @param Zend\Code\Reflection\DocBlockReflection $doc
     * @return string
     */
    protected function _getDescription(\Zend\Code\Reflection\DocBlockReflection $doc)
    {
        $shortDescription = $doc->getShortDescription();
        $longDescription = $doc->getLongDescription();

        $description = $shortDescription;
        if ($longDescription && !empty($description)) {
            $description .= "\r\n";
        }
        $description .= $longDescription;

        return $description;
    }
}
