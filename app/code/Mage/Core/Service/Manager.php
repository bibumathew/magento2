<?php

class Mage_Core_Service_Manager extends Varien_Object
{
    const AREA_SERVICES = 'services';

    /** @var Mage_Core_Service_Factory */
    protected $_serviceFactory;

    /** @var Mage_Core_Service_Context */
    protected $_serviceContext;

    /**
     * @var array $_requestSchemas
     */
    protected $_requestSchemas = array();

    /**
     * @var array $_responseSchemas
     */
    protected $_responseSchemas = array();

    /**
     * @var array $_contentSchemas
     */
    protected $_contentSchemas = array();

    public function __construct(
        Mage_Core_Service_Factory $serviceFactory,
        Mage_Core_Service_Context $serviceContext)
    {
        $this->_serviceFactory = $serviceFactory;
        $this->_serviceContext = $serviceContext;
    }

    /**
     * Call a service method
     *
     * @param string $serviceClass
     * @param string $serviceMethod
     * @param mixed $context [optional]
     * @return mixed (service execution response)
     */
    public function call($serviceClass, $serviceMethod, $context = null)
    {
        $service  = $this->getService($serviceClass);

        $response = $service->call($serviceMethod, $context);

        return $response;
    }

    /**
     * Retrieve a service instance
     *
     * @param string $serviceId
     * @return Mage_Core_Service_Type_Abstract $service
     */
    public function getService($serviceId)
    {
        $service = $this->_serviceFactory->createServiceInstance($serviceId);
        return $service;
    }

    /**
     * @param string $serviceClass
     * @param string $serviceMethod [optional]
     * @param string $version [optional]
     * @param array $extraParameters [optional]
     * @return Magento_Data_Schema $requestSchema
     */
    public function getRequestSchema($serviceClass, $serviceMethod = null, $version = null, array $extraParameters = array())
    {
        $hash = $serviceClass . '::' . $serviceMethod . '::' . $version;
        if (!isset($this->_requestSchemas[$hash])) {
            $schema = array();
            $_clsPath = explode('_', $serviceClass);
            $serviceSchemaPath = "app/code/{$_clsPath[0]}/{$_clsPath[1]}/etc/service/$serviceClass/request.php";
            if (is_file($serviceSchemaPath)) {
                include $serviceSchemaPath;
            }
            $resultSchema = $schema;
            if (null !== $serviceMethod) {
                $schema = array();
                $methodSchemaPath = "app/code/{$_clsPath[0]}/{$_clsPath[1]}/etc/service/$serviceClass/$serviceMethod/request.php";
                if (is_file($methodSchemaPath)) {
                    include $methodSchemaPath;
                }
                $resultSchema = array_merge($resultSchema, $schema);
            }

            if (!empty($extraParameters)) {
                $resultSchema = array_merge($resultSchema, $extraParameters);
            }

            $this->_requestSchemas[$hash] = new Magento_Data_Schema();
            $this->_requestSchemas[$hash]->load($resultSchema);
        }

        return $this->_requestSchemas[$hash];
    }

    /**
     * @param string $serviceClass
     * @param string $serviceMethod [optional]
     * @param string $version [optional]
     * @return Magento_Data_Schema $responseSchema
     */
    public function getResponseSchema($serviceClass, $serviceMethod = null, $version = null)
    {
        $hash = $serviceClass . '::' . $serviceMethod . '::' . $version;
        if (!isset($this->_responseSchemas[$hash])) {
            $schema = array();
            $_clsPath = explode('_', $serviceClass);
            $serviceSchemaPath = "app/code/{$_clsPath[0]}/{$_clsPath[1]}/etc/service/$serviceClass/response.php";
            if (is_file($serviceSchemaPath)) {
                include $serviceSchemaPath;
            }
            $resultSchema = $schema;
            if (null !== $serviceMethod) {
                $schema = array();
                $methodSchemaPath = "app/code/{$_clsPath[0]}/{$_clsPath[1]}/etc/service/$serviceClass/$serviceMethod/response.php";
                if (is_file($methodSchemaPath)) {
                    include $methodSchemaPath;
                }
                $resultSchema = array_merge($resultSchema, $schema);
            }

            $this->_responseSchemas[$hash] = new Magento_Data_Schema();
            $this->_responseSchemas[$hash]->load($resultSchema);
        }

        return $this->_responseSchemas[$hash];
    }

    /**
     * @param mixed $schemaFile
     * @return Magento_Data_Schema $contentSchema
     */
    public function getContentSchema($schemaFile)
    {
        if (!isset($this->_contentSchemas[$schemaFile])) {
            $this->_contentSchemas[$schemaFile] = new Magento_Data_Schema();
            $this->_contentSchemas[$schemaFile]->load($schemaFile);
        }

        return $this->_contentSchemas[$schemaFile];
    }
}

/**
 * @todo this is a prototype
 *
 * Service definitions may be area-specific with an ability to be extended and replaced for target area
 * or better to say for some unique global context
 *
 * Eg, in default case we work with default definitions while when application runs in "safe" mode
 * we have a specific subset of definitions which extend/overrides the default ones
 *
 * Regarding versioning: we design all "internal" definitions to be 100% matched to WEB API needs.
 * When we bootstrap new WEB API we use all "internal" definitions as a first version of WEB API.
 * All upcoming changes for "internal" usage should be implemented within new files
 * and not directly in original definition files.
 * Following this way we have clear way of controlling of when we should introduce new WEB API version.
 * Also we won't need to "replicate" entirely all "internal" definitions to have WEB API version independent (meaning stable).
 *
 */