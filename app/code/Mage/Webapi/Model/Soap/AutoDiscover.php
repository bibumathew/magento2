<?php
use Zend\Soap\Wsdl;

/**
 * Auto discovery tool for WSDL generation from Magento web API configuration.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Model_Soap_AutoDiscover
{
    const WSDL_NAME = 'MagentoWSDL';

    /**
     * Cache ID for generated WSDL content.
     */
    const WSDL_CACHE_ID = 'WSDL';

    /**
     * API config instance.
     * Used to retrieve complex types data.
     *
     * @var Mage_Webapi_Config
     */
    protected $_apiConfig;

    /**
     * TODO: Temporary variable for step-by-step refactoring according to new requirements
     *
     * @var Mage_Webapi_Config
     */
    protected $_newApiConfig;

    /**
     * WSDL factory instance.
     *
     * @var Mage_Webapi_Model_Soap_Wsdl_Factory
     */
    protected $_wsdlFactory;

    /**
     * @var Mage_Webapi_Helper_Config
     */
    protected $_helper;

    /** @var Mage_Core_Model_CacheInterface */
    protected $_cache;

    /**
     * Construct auto discover with resource config and list of requested resources.
     *
     * @param Mage_Webapi_Config $newApiConfig
     * @param Mage_Webapi_Model_Config_Soap $apiConfig
     * @param Mage_Webapi_Model_Soap_Wsdl_Factory $wsdlFactory
     * @param Mage_Webapi_Helper_Config $helper
     * @param Mage_Core_Model_CacheInterface $cache
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        Mage_Webapi_Config $newApiConfig,
        Mage_Webapi_Model_Config_Soap $apiConfig,
        Mage_Webapi_Model_Soap_Wsdl_Factory $wsdlFactory,
        Mage_Webapi_Helper_Config $helper,
        Mage_Core_Model_CacheInterface $cache
    )
    {
        $this->_apiConfig = $apiConfig;
        $this->_newApiConfig = $newApiConfig;
        $this->_wsdlFactory = $wsdlFactory;
        $this->_helper = $helper;
        $this->_cache = $cache;
    }

    /**
     * Generate WSDL content and save it to cache.
     *
     * @param array $requestedResources
     * @param string $endpointUrl
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function handle($requestedResources, $endpointUrl)
    {
        /** TODO: Remove Mage_Catalog_Service_Product after this method is finalized */
        /** Sort requested resources by names to prevent caching of the same wsdl file more than once. */
        ksort($requestedResources);
//        $cacheId = self::WSDL_CACHE_ID . hash('md5', serialize($requestedResources));
//        if ($this->_cache->canUse(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_NAME)) {
//            $cachedWsdlContent = $this->_cache->load($cacheId);
//            if ($cachedWsdlContent !== false) {
//                return $cachedWsdlContent;
//            }
//        }

        $resources = array();
        foreach ($this->_newApiConfig->getServices() as $serviceData) {
            $serviceClass = $serviceData['class'];
            $resourceName = $this->_helper->translateResourceName($serviceClass);
            $resources[$resourceName] = array('methods' => array());
            foreach ($serviceData['operations'] as $operationData) {
                $serviceMethod = $operationData['method'];

                /** @var  $dom DOMDocument */
                $dom = $this->_getServiceSchemaDOM($serviceClass);

                $operationName = $this->getOperationName($resourceName, $serviceMethod);

                $inputParameterName = $this->getInputMessageName($operationName);
                $inputComplexTypeNode = $this->_getComplexTypeNode($inputParameterName, $dom);
                if (!empty($inputComplexTypeNode)) {
                    $resources[$resourceName]['methods'][$serviceMethod]['interface']['in']['schema'] =
                        $inputComplexTypeNode;
                }

                $outputParameterName = $this->getOutputMessageName($operationName);
                $outputComplexTypeNode = $this->_getComplexTypeNode($outputParameterName, $dom);
                if (!empty($outputComplexTypeNode)) {
                    $resources[$resourceName]['methods'][$serviceMethod]['interface']['out']['schema'] =
                        $outputComplexTypeNode;
                }
            }
        }
        $wsdlContent = $this->generate($resources, $endpointUrl);

//        if ($this->_cache->canUse(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_NAME)) {
//            $this->_cache->save($wsdlContent, $cacheId, array(Mage_Webapi_Model_ConfigAbstract::WEBSERVICE_CACHE_TAG));
//        }

        return $wsdlContent;
    }

    /**
     * Method to load Service specific XSD
     *
     * @param $serviceName
     * @return DOMDocument
     */
    protected function _getServiceSchemaDOM($serviceName)
    {
        return $this->_newApiConfig->getServiceSchemaDOM($serviceName);
    }


    /**
     * @param $parameterName string Name of the input or output parameter
     * @param $dom DOMDocument
     * @return DOMNode
     */
    protected function _getComplexTypeNode($parameterName, $dom)
    {
        $xpath = new DOMXPath($dom);
        /** @var $elemList DOMNodeList */
        $elemList = $xpath->query("//xsd:complexType[@name='$parameterName']");
        /**  @var $elem DOMElement */
        $elem = $elemList->item(0); //item(0) returns DOMElement even though it says it returns DOMNode
        /** @var $node DOMNode */
        $node = $elem->cloneNode(true);

        return $node;
    }

    /**
     * Generate WSDL file based on requested resources.
     *
     * @param array $requestedResources
     * @param string $endPointUrl
     * @return string
     */
    public function generate($requestedResources, $endPointUrl)
    {
        $wsdl = $this->_wsdlFactory->create(self::WSDL_NAME, $endPointUrl);
        $wsdl->addSchemaTypeSection();

        foreach ($requestedResources as $resourceName => $resourceData) {
            $portTypeName = $this->getPortTypeName($resourceName);
            $bindingName = $this->getBindingName($resourceName);
            $portType = $wsdl->addPortType($portTypeName);
            $binding = $wsdl->addBinding($bindingName, Wsdl::TYPES_NS . ':' . $portTypeName);
            $wsdl->addSoapBinding($binding, 'document', 'http://schemas.xmlsoap.org/soap/http', SOAP_1_2);
            $portName = $this->getPortName($resourceName);
            $serviceName = $this->getServiceName($resourceName);
            $wsdl->addService($serviceName, $portName, 'tns:' . $bindingName, $endPointUrl, SOAP_1_2);

            foreach ($resourceData['methods'] as $methodName => $methodData) {
                $operationName = $this->getOperationName($resourceName, $methodName);
                $inputBinding = array('use' => 'literal');
                $inputMessageName = $this->_createOperationInput($wsdl, $operationName, $methodData);

                $outputMessageName = false;
                $outputBinding = false;
                if (isset($methodData['interface']['out']['schema'])) {
                    $outputBinding = $inputBinding;
                    $outputMessageName = $this->_createOperationOutput($wsdl, $operationName, $methodData);
                }

                $wsdl->addPortOperation($portType, $operationName, $inputMessageName, $outputMessageName);
                $bindingOperation = $wsdl->addBindingOperation(
                    $binding,
                    $operationName,
                    $inputBinding,
                    $outputBinding,
                    false,
                    SOAP_1_2
                );
                $wsdl->addSoapOperation($bindingOperation, $operationName, SOAP_1_2);
                // @TODO: implement faults binding
            }
        }

        return $wsdl->toXML();
    }

    /**
     * Create input message and corresponding element and complex types in WSDL.
     *
     * @param Mage_Webapi_Model_Soap_Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string input message name
     */
    protected function _createOperationInput(Mage_Webapi_Model_Soap_Wsdl $wsdl, $operationName, $methodData)
    {
        /**
         * TODO: Make sure that complex type name is taken from a single place
         *
         * (currently we have two sources: XSD schema
         * and auto-generation mechanism: $this->getElementComplexTypeName($inputMessageName))
         */
        $inputMessageName = $this->getInputMessageName($operationName);
        //$complexTypeName = $this->getElementComplexTypeName($inputMessageName);
        $elementData = array(
            'name' => $inputMessageName,
            'type' => Wsdl::TYPES_NS . ':' . $inputMessageName
        );
        if (isset($methodData['interface']['in']['schema'])) {
            $inputComplexTypeNode = $methodData['interface']['in']['schema'];
            $wsdl->addComplexType($inputComplexTypeNode);
        } else {
            $elementData['nillable'] = 'true';
        }
        $wsdl->addElement($elementData);
        $wsdl->addMessage(
            $inputMessageName,
            array(
                'messageParameters' => array(
                    'element' => Wsdl::TYPES_NS . ':' . $inputMessageName
                )
            )
        );

        return Wsdl::TYPES_NS . ':' . $inputMessageName;
    }

    /**
     * Create output message and corresponding element and complex types in WSDL.
     *
     * @param Mage_Webapi_Model_Soap_Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string output message name
     */
    protected function _createOperationOutput(Mage_Webapi_Model_Soap_Wsdl $wsdl, $operationName, $methodData)
    {
        $outputMessageName = $this->getOutputMessageName($operationName);
        //$complexTypeName = $this->getElementComplexTypeName($outputMessageName);
        $wsdl->addElement(
            array(
                'name' => $outputMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $outputMessageName
            )
        );
        if (isset($methodData['interface']['out']['schema'])) {
            $outputComplexTypeNode = $methodData['interface']['out']['schema'];
            $wsdl->addComplexType($outputComplexTypeNode);
        }
        $wsdl->addMessage(
            $outputMessageName,
            array(
                'messageParameters' => array(
                    'element' => Wsdl::TYPES_NS . ':' . $outputMessageName
                )
            )
        );

        return Wsdl::TYPES_NS . ':' . $outputMessageName;
    }

    /**
     * Get name of complexType for message element.
     *
     * @param string $messageName
     * @return string
     */
    public function getElementComplexTypeName($messageName)
    {
        return ucfirst($messageName);
    }

    /**
     * Get name for resource portType node.
     *
     * @param string $resourceName
     * @return string
     */
    public function getPortTypeName($resourceName)
    {
        return $resourceName . 'PortType';
    }

    /**
     * Get name for resource binding node.
     *
     * @param string $resourceName
     * @return string
     */
    public function getBindingName($resourceName)
    {
        return $resourceName . 'Binding';
    }

    /**
     * Get name for resource port node.
     *
     * @param string $resourceName
     * @return string
     */
    public function getPortName($resourceName)
    {
        return $resourceName . 'Port';
    }

    /**
     * Get name for resource service.
     *
     * @param string $resourceName
     * @return string
     */
    public function getServiceName($resourceName)
    {
        return $resourceName . 'Service';
    }

    /**
     * Get name of operation based on resource and method names.
     *
     * @param string $resourceName
     * @param string $methodName
     * @return string
     */
    public function getOperationName($resourceName, $methodName)
    {
        return $resourceName . ucfirst($methodName);
    }

    /**
     * Get input message node name for operation.
     *
     * @param string $operationName
     * @return string
     */
    public function getInputMessageName($operationName)
    {
        return $operationName . 'Request';
    }

    /**
     * Get output message node name for operation.
     *
     * @param string $operationName
     * @return string
     */
    public function getOutputMessageName($operationName)
    {
        return $operationName . 'Response';
    }
}
